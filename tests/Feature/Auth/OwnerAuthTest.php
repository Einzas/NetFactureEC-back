<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OwnerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    /** @test */
    public function owner_can_login_with_valid_credentials()
    {
        $owner = User::create([
            'type' => 'owner',
            'name' => 'John Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/owner/login', [
            'email' => 'owner@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ]
                ]
            ]);

        $this->assertTrue($response->json('success'));
    }

    /** @test */
    public function owner_cannot_login_with_invalid_password()
    {
        User::create([
            'type' => 'owner',
            'name' => 'John Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/owner/login', [
            'email' => 'owner@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function owner_can_get_dashboard_data()
    {
        $owner = User::create([
            'type' => 'owner',
            'name' => 'John Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        // Crear empresas
        Company::create([
            'owner_id' => $owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company 1',
            'trade_name' => 'Test 1',
            'address' => 'Address 1',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'email' => 'test1@test.com',
            'is_active' => true,
        ]);

        Company::create([
            'owner_id' => $owner->id,
            'ruc' => '1790123456002',
            'business_name' => 'Test Company 2',
            'trade_name' => 'Test 2',
            'address' => 'Address 2',
            'city' => 'Guayaquil',
            'province' => 'Guayas',
            'email' => 'test2@test.com',
            'is_active' => false,
        ]);

        $token = auth('owner')->login($owner);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/owner/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_companies',
                    'active_companies',
                    'total_employees',
                    'active_employees',
                    'total_files',
                    'storage_used_mb',
                ]
            ]);

        $this->assertEquals(2, $response->json('data.total_companies'));
        $this->assertEquals(1, $response->json('data.active_companies'));
    }

    /** @test */
    public function owner_can_logout()
    {
        $owner = User::create([
            'type' => 'owner',
            'name' => 'John Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $token = auth('owner')->login($owner);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/owner/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
            ]);
    }

    /** @test */
    public function owner_can_refresh_token()
    {
        $owner = User::create([
            'type' => 'owner',
            'name' => 'John Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $token = auth('owner')->login($owner);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/owner/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'token_type',
                    'expires_in',
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_request_returns_401()
    {
        $response = $this->getJson('/api/v1/owner/dashboard');

        $response->assertStatus(401);
    }
}
