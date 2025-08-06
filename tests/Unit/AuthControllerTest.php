<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mockery;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AuthController();
    }

    public function test_show_login_returns_login_view(): void
    {
        $response = $this->controller->showLogin();

        $this->assertEquals('login', $response->getName());
    }

    public function test_login_with_valid_credentials_succeeds(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        $request->setLaravelSession($this->app['session.store']);

        $response = $this->controller->login($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Login successful!', $responseData['message']);

        $this->assertDatabaseHas('token', [
            'user_id' => $user->id,
        ]);
    }

    public function test_login_with_invalid_credentials_fails(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);
        $request->setLaravelSession($this->app['session.store']);

        $response = $this->controller->login($request);
        $responseData = $response->getData(true);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Invalid login credentials!', $responseData['message']);

        $this->assertDatabaseMissing('token', [
            'user_id' => 1,
        ]);
    }

    public function test_login_creates_token_with_correct_expiry(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $request = Request::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        $request->setLaravelSession($this->app['session.store']);

        $this->controller->login($request);

        $token = DB::table('token')->where('user_id', $user->id)->first();

        $this->assertNotNull($token);
        $this->assertEquals(AuthController::TOKEN_LENGTH, strlen($token->token));

        $expectedExpiry = now()->addDays(AuthController::TOKEN_EXPIRY_DAYS)->format('Y-m-d H:i');
        $actualExpiry = \Carbon\Carbon::parse($token->expires_at)->format('Y-m-d H:i');
        $this->assertEquals($expectedExpiry, $actualExpiry);
    }

    public function test_logout_clears_auth_and_session(): void
    {
        $user = User::factory()->create();

        $request = Request::create('/logout', 'POST');
        $request->setLaravelSession($this->app['session.store']);

        Auth::login($user);
        $this->assertTrue(Auth::check());

        $response = $this->controller->logout($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Successfully logged out!', $responseData['message']);
        $this->assertFalse(Auth::check());
    }

    public function test_register_with_valid_data_creates_user(): void
    {
        $request = Request::create('/register', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);

        $response = $this->controller->register($request);
        $responseData = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Registration successful!', $responseData['message']);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertTrue(Auth::check());
    }

    public function test_register_with_duplicate_email_fails(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $request = Request::create('/register', 'POST', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123'
        ]);

        $this->controller->register($request);
    }

    public function test_register_with_invalid_data_fails(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $request = Request::create('/register', 'POST', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123'
        ]);

        $this->controller->register($request);
    }

    public function test_register_hashes_password(): void
    {
        $request = Request::create('/register', 'POST', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);

        $this->controller->register($request);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNotEquals('password123', $user->password);
    }

    public function test_token_constants_are_defined(): void
    {
        $this->assertEquals(1, AuthController::TOKEN_EXPIRY_DAYS);
        $this->assertEquals(10, AuthController::COOKIE_EXPIRY_DAYS);
        $this->assertEquals(60, AuthController::TOKEN_LENGTH);
    }
}