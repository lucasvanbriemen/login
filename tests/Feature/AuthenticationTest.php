<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Login successful!',
        ]);

        $this->assertAuthenticatedAs($user);
        
        $this->assertDatabaseHas('token', [
            'user_id' => $user->id,
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid login credentials!',
        ]);

        $this->assertGuest();
        
        $this->assertDatabaseMissing('token', [
            'user_id' => $user->id,
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid login credentials!',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully logged out!',
        ]);

        $this->assertGuest();
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Registration successful!',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        
        $this->assertEquals(1, User::where('email', 'test@example.com')->count());
    }

    public function test_user_cannot_register_with_invalid_email(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        
        $this->assertDatabaseMissing('users', [
            'name' => 'Test User',
        ]);
    }

    public function test_user_cannot_register_with_short_password(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '1234567', // 7 characters, minimum is 8
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_cannot_register_without_name(): void
    {
        $response = $this->postJson('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
        
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_cannot_register_with_too_long_name(): void
    {
        $longName = str_repeat('a', 256); // 256 characters, max is 255

        $response = $this->postJson('/register', [
            'name' => $longName,
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
        
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_login_creates_auth_token_cookie(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        
        $token = DB::table('token')->where('user_id', $user->id)->first();
        $this->assertNotNull($token);
        $this->assertEquals(60, strlen($token->token)); // TOKEN_LENGTH = 60
    }

    public function test_logout_clears_auth_token_cookie(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully logged out!',
        ]);
    }

    public function test_login_regenerates_session(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->startSession();
        $oldSessionId = $this->app['session']->getId();

        $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $newSessionId = $this->app['session']->getId();
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $this->startSession();
        $oldSessionId = $this->app['session']->getId();

        $this->postJson('/logout');

        $newSessionId = $this->app['session']->getId();
        $this->assertNotEquals($oldSessionId, $newSessionId);
    }
}