<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    /** @test */
    public function employee_can_login_with_valid_credentials()
    {
        $owner = User::create([
            'type' => 'owner',
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $company = Company::create([
            'owner_id' => $owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'email' => 'company@test.com',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'company_id' => $company->id,
            'email' => 'employee@test.com',
            'password' => bcrypt('password123'),
            'name' => 'John Employee',
            'identification' => '1234567890001',
            'phone' => '+593991234567',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/employee/login', [
            'email' => 'employee@test.com',
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
                    'employee' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'company',
                    ],
                    'roles',
                    'permissions',
                ]
            ]);
    }

    /** @test */
    public function employee_cannot_login_when_inactive()
    {
        $owner = User::create([
            'type' => 'owner',
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $company = Company::create([
            'owner_id' => $owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'email' => 'company@test.com',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'company_id' => $company->id,
            'email' => 'employee@test.com',
            'password' => bcrypt('password123'),
            'name' => 'Inactive Employee',
            'identification' => '1234567890001',
            'phone' => '+593991234567',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/employee/login', [
            'email' => 'employee@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
    }

    /** @test */
    public function employee_cannot_login_when_company_inactive()
    {
        $owner = User::create([
            'type' => 'owner',
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $company = Company::create([
            'owner_id' => $owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Inactive Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'email' => 'company@test.com',
            'is_active' => false,
        ]);

        $employee = Employee::create([
            'company_id' => $company->id,
            'email' => 'employee@test.com',
            'password' => bcrypt('password123'),
            'name' => 'Employee',
            'identification' => '1234567890001',
            'phone' => '+593991234567',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/employee/login', [
            'email' => 'employee@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'La empresa se encuentra inactiva. Contacte al administrador.',
            ]);
    }

    /** @test */
    public function employee_can_get_profile()
    {
        $owner = User::create([
            'type' => 'owner',
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $company = Company::create([
            'owner_id' => $owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'email' => 'company@test.com',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'company_id' => $company->id,
            'email' => 'employee@test.com',
            'password' => bcrypt('password123'),
            'name' => 'John Employee',
            'identification' => '1234567890001',
            'phone' => '+593991234567',
            'is_active' => true,
        ]);

        $token = auth('employee')->login($employee);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/employee/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'identification',
                    'phone',
                    'is_active',
                    'last_login_at',
                    'company' => [
                        'id',
                        'business_name',
                        'trade_name',
                    ],
                    'roles',
                    'permissions',
                ]
            ]);
    }

    /** @test */
    public function employee_can_logout()
    {
        $owner = User::create([
            'type' => 'owner',
            'name' => 'Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $company = Company::create([
            'owner_id' => $owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'email' => 'company@test.com',
            'is_active' => true,
        ]);

        $employee = Employee::create([
            'company_id' => $company->id,
            'email' => 'employee@test.com',
            'password' => bcrypt('password123'),
            'name' => 'John Employee',
            'identification' => '1234567890001',
            'phone' => '+593991234567',
            'is_active' => true,
        ]);

        $token = auth('employee')->login($employee);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/employee/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente',
            ]);
    }
}
