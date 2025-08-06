<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_extends_authenticatable(): void
    {
        $this->assertInstanceOf(Authenticatable::class, new User());
    }

    public function test_user_model_uses_has_factory_trait(): void
    {
        $this->assertContains(HasFactory::class, class_uses(User::class));
    }

    public function test_user_model_uses_notifiable_trait(): void
    {
        $this->assertContains(Notifiable::class, class_uses(User::class));
    }

    public function test_fillable_attributes_are_correctly_defined(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $expectedFillable = ['name', 'email', 'password'];
        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_hidden_attributes_are_correctly_defined(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
            'remember_token' => 'remember_token_value'
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
    }

    public function test_password_is_hashed_when_cast(): void
    {
        $user = User::factory()->create([
            'password' => 'plaintext_password'
        ]);

        $this->assertNotEquals('plaintext_password', $user->password);
        $this->assertTrue(Hash::check('plaintext_password', $user->password));
    }

    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $casts = $user->getCasts();
        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertInstanceOf(\DateTime::class, $user->email_verified_at);
    }

    public function test_user_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email
        ]);
    }

    public function test_user_factory_creates_unique_emails(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->assertNotEquals($user1->email, $user2->email);
    }

    public function test_user_can_be_created_with_specific_attributes(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123'
        ];

        $user = User::factory()->create($userData);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_user_timestamps_are_automatically_managed(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertTrue($user->created_at->equalTo($user->updated_at));
    }

    public function test_user_updated_at_changes_when_modified(): void
    {
        $user = User::factory()->create();
        $originalUpdatedAt = $user->updated_at;

        // Wait a moment and update
        sleep(1);
        $user->name = 'Updated Name';
        $user->save();

        $this->assertNotEquals($originalUpdatedAt, $user->updated_at);
    }

    public function test_user_model_has_correct_table_name(): void
    {
        $user = new User();
        $this->assertEquals('users', $user->getTable());
    }

    public function test_user_model_primary_key_is_id(): void
    {
        $user = new User();
        $this->assertEquals('id', $user->getKeyName());
    }

    public function test_user_model_uses_incremented_primary_key(): void
    {
        $user = new User();
        $this->assertTrue($user->getIncrementing());
    }

    public function test_user_model_key_type_is_int(): void
    {
        $user = new User();
        $this->assertEquals('int', $user->getKeyType());
    }

    public function test_user_can_be_soft_deleted_if_trait_exists(): void
    {
        // Check if SoftDeletes trait is used
        $traits = class_uses_recursive(User::class);
        
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits)) {
            $user = User::factory()->create();
            $user->delete();
            
            $this->assertSoftDeleted($user);
        } else {
            // If SoftDeletes is not used, user is hard deleted
            $user = User::factory()->create();
            $userId = $user->id;
            $user->delete();
            
            $this->assertDatabaseMissing('users', ['id' => $userId]);
        }
    }

    public function test_user_mass_assignment_protection(): void
    {
        $user = new User();
        
        // Try to mass assign non-fillable attribute
        $user->fill([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret123',
            'id' => 999, // Not in fillable
            'created_at' => now(), // Not in fillable
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        // Password gets hashed due to the 'hashed' cast, so we check if it's hashed
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertNull($user->id); // Should not be set via mass assignment
        $this->assertNull($user->created_at); // Should not be set via mass assignment
    }

    public function test_user_attributes_are_properly_cast(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => '2023-01-01 12:00:00'
        ]);

        $casts = $user->getCasts();
        
        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
        
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('hashed', $casts['password']);
    }

    public function test_user_json_serialization_excludes_hidden_fields(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
            'remember_token' => 'token123'
        ]);

        $json = $user->toJson();
        $data = json_decode($json, true);

        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('remember_token', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('id', $data);
    }
}