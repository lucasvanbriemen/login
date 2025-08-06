<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class TokenValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createTokenForUser(User $user, ?Carbon $expiresAt = null): string
    {
        $token = bin2hex(random_bytes(30)); // 60 character token
        
        DB::table('token')->insert([
            'token' => $token,
            'user_id' => $user->id,
            'expires_at' => $expiresAt ?? now()->addDays(1),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $token;
    }

    public function test_valid_token_returns_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $token = $this->createTokenForUser($user);

        $response = $this->get("/api/user/token/{$token}");

        $response->assertStatus(200);
        $response->assertJson([
            'user' => [
                'id' => $user->id,
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ]
        ]);

        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_valid_token_updates_user_last_activity(): void
    {
        $user = User::factory()->create([
            'last_activity' => null
        ]);

        $token = $this->createTokenForUser($user);

        $this->get("/api/user/token/{$token}");

        $user->refresh();
        $this->assertNotNull($user->last_activity);
        $this->assertTrue(now()->diffInSeconds($user->last_activity) < 5);
    }

    public function test_nonexistent_token_returns_404(): void
    {
        $nonExistentToken = 'nonexistent' . bin2hex(random_bytes(25));

        $response = $this->get("/api/user/token/{$nonExistentToken}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Token not found'
        ]);
    }

    public function test_expired_token_returns_401_and_deletes_token(): void
    {
        $user = User::factory()->create();
        $expiredDate = now()->subDays(1); // 1 day ago
        
        $token = $this->createTokenForUser($user, $expiredDate);

        $response = $this->get("/api/user/token/{$token}");

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Token has expired'
        ]);

        $this->assertDatabaseMissing('token', [
            'token' => $token
        ]);
    }

    public function test_token_expiring_soon_still_works(): void
    {
        $user = User::factory()->create();
        $expiresInOneHour = now()->addHour();
        
        $token = $this->createTokenForUser($user, $expiresInOneHour);

        $response = $this->get("/api/user/token/{$token}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email'
            ]
        ]);
    }

    public function test_token_expiring_in_exact_moment_returns_401(): void
    {
        $user = User::factory()->create();
        $expiresNow = now(); // Expires exactly now
        
        $token = $this->createTokenForUser($user, $expiresNow);

        // Travel 1 second into future to ensure token is expired
        $this->travel(1)->seconds();

        $response = $this->get("/api/user/token/{$token}");

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Token has expired'
        ]);
    }

    public function test_multiple_valid_tokens_for_same_user_work(): void
    {
        $user = User::factory()->create();
        
        $token1 = $this->createTokenForUser($user);
        $token2 = $this->createTokenForUser($user);

        $response1 = $this->get("/api/user/token/{$token1}");
        $response2 = $this->get("/api/user/token/{$token2}");

        $response1->assertStatus(200);
        $response2->assertStatus(200);
        
        $this->assertEquals(
            $response1->json('user.id'),
            $response2->json('user.id')
        );
    }

    public function test_deleted_user_token_still_exists_but_no_user_found(): void
    {
        $user = User::factory()->create();
        $token = $this->createTokenForUser($user);
        
        $user->delete();

        $response = $this->get("/api/user/token/{$token}");

        $response->assertStatus(500);
    }

    public function test_token_validation_with_special_characters(): void
    {
        $user = User::factory()->create();
        
        // Create token with special characters that should be URL safe
        $specialToken = 'abc123-_' . bin2hex(random_bytes(23));
        
        DB::table('token')->insert([
            'token' => $specialToken,
            'user_id' => $user->id,
            'expires_at' => now()->addDays(1),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get("/api/user/token/{$specialToken}");

        $response->assertStatus(200);
        $response->assertJson([
            'user' => [
                'id' => $user->id
            ]
        ]);
    }

    public function test_empty_token_parameter_returns_404(): void
    {
        $response = $this->get("/api/user/token/");

        $response->assertStatus(404);
    }

    public function test_very_long_token_parameter_handled_safely(): void
    {
        $veryLongToken = str_repeat('a', 1000);

        $response = $this->get("/api/user/token/{$veryLongToken}");

        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'Token not found'
        ]);
    }

    public function test_token_validation_case_sensitive(): void
    {
        $user = User::factory()->create();
        $token = $this->createTokenForUser($user);
        
        $upperCaseToken = strtoupper($token);

        if ($token !== $upperCaseToken) {
            $response = $this->get("/api/user/token/{$upperCaseToken}");
            $response->assertStatus(404);
        }
    }

    public function test_concurrent_token_validations_work(): void
    {
        $user = User::factory()->create();
        $token = $this->createTokenForUser($user);

        // Simulate concurrent requests
        $responses = [];
        for ($i = 0; $i < 3; $i++) {
            $responses[] = $this->get("/api/user/token/{$token}");
        }

        foreach ($responses as $response) {
            $response->assertStatus(200);
            $response->assertJson([
                'user' => [
                    'id' => $user->id
                ]
            ]);
        }
    }

    public function test_token_with_null_user_id_handled_gracefully(): void
    {
        $token = bin2hex(random_bytes(30));
        
        DB::table('token')->insert([
            'token' => $token,
            'user_id' => null,
            'expires_at' => now()->addDays(1),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get("/api/user/token/{$token}");

        $response->assertStatus(500);
    }
}