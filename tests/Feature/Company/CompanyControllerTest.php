<?php

namespace Tests\Feature\Company;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $this->owner = User::create([
            'type' => 'owner',
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Owners no necesitan permisos, tienen acceso automático a sus empresas
        $this->token = auth('owner')->login($this->owner);
    }

    /** @test */
    public function owner_can_list_their_companies()
    {
        // Crear empresas
        Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Company 1',
            'trade_name' => 'C1',
            'address' => 'Address 1',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'email' => 'c1@test.com',
            'is_active' => true,
        ]);

        Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456002',
            'business_name' => 'Company 2',
            'trade_name' => 'C2',
            'address' => 'Address 2',
            'city' => 'Guayaquil',
            'province' => 'Guayas',
            'email' => 'c2@test.com',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/owner/companies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'ruc',
                            'business_name',
                            'trade_name',
                            'address',
                            'city',
                            'province',
                            'email',
                            'is_active',
                            'employees_count',
                        ]
                    ]
                ]
            ]);

        $this->assertEquals(2, count($response->json('data.data')));
    }

    /** @test */
    public function owner_can_create_company()
    {
        $companyData = [
            'ruc' => '1790123456001',
            'business_name' => 'New Company S.A.',
            'trade_name' => 'New Company',
            'address' => 'Av. Principal 123',
            'city' => 'Quito',
            'province' => 'Pichincha',
            'phone' => '+593987654321',
            'email' => 'new@company.com',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/owner/companies', $companyData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'ruc',
                    'business_name',
                    'trade_name',
                    'owner' => ['id', 'name', 'email']
                ]
            ]);

        $this->assertDatabaseHas('companies', [
            'ruc' => '1790123456001',
            'business_name' => 'New Company S.A.',
            'owner_id' => $this->owner->id,
        ]);
    }

    /** @test */
    public function ruc_must_be_13_digits()
    {
        $companyData = [
            'ruc' => '123456789', // Solo 9 dígitos
            'business_name' => 'Test Company',
            'address' => 'Address',
            'email' => 'test@test.com',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/owner/companies', $companyData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ruc']);
    }

    /** @test */
    public function ruc_must_be_numeric()
    {
        $companyData = [
            'ruc' => '179012345600A', // Contiene letra
            'business_name' => 'Test Company',
            'address' => 'Address',
            'email' => 'test@test.com',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/owner/companies', $companyData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ruc']);
    }

    /** @test */
    public function ruc_must_be_unique()
    {
        Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Existing Company',
            'trade_name' => 'Existing',
            'address' => 'Address',
            'email' => 'existing@test.com',
            'is_active' => true,
        ]);

        $companyData = [
            'ruc' => '1790123456001', // RUC duplicado
            'business_name' => 'New Company',
            'address' => 'Address',
            'email' => 'new@test.com',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/owner/companies', $companyData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ruc']);
    }

    /** @test */
    public function email_must_be_unique()
    {
        Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Existing Company',
            'trade_name' => 'Existing',
            'address' => 'Address',
            'email' => 'existing@test.com',
            'is_active' => true,
        ]);

        $companyData = [
            'ruc' => '1790123456002',
            'business_name' => 'New Company',
            'address' => 'Address',
            'email' => 'existing@test.com', // Email duplicado
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/owner/companies', $companyData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function owner_can_view_specific_company()
    {
        $company = Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'email' => 'test@test.com',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/owner/companies/' . $company->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'ruc',
                    'business_name',
                    'owner' => ['id', 'name'],
                    'employees_count',
                ]
            ]);
    }

    /** @test */
    public function owner_cannot_view_other_owners_company()
    {
        $otherOwner = User::create([
            'name' => 'Other Owner',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'type' => 'owner',
            'is_active' => true,
        ]);

        $company = Company::create([
            'owner_id' => $otherOwner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Other Company',
            'trade_name' => 'Other',
            'address' => 'Address',
            'email' => 'other@company.com',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/owner/companies/' . $company->id);

        $response->assertStatus(404);
    }

    /** @test */
    public function owner_can_update_their_company()
    {
        $company = Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Old Name',
            'trade_name' => 'Old',
            'address' => 'Old Address',
            'email' => 'old@test.com',
            'is_active' => true,
        ]);

        $updateData = [
            'business_name' => 'Updated Name',
            'address' => 'New Address',
            'phone' => '+593999888777',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/owner/companies/' . $company->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Empresa actualizada exitosamente',
            ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'business_name' => 'Updated Name',
            'address' => 'New Address',
            'phone' => '+593999888777',
        ]);
    }

    /** @test */
    public function owner_can_toggle_company_status()
    {
        $company = Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'email' => 'test@test.com',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/v1/owner/companies/' . $company->id . '/toggle-status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Empresa desactivada',
            ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function owner_can_soft_delete_company()
    {
        $company = Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'email' => 'test@test.com',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/owner/companies/' . $company->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Empresa eliminada exitosamente',
            ]);

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    /** @test */
    public function cannot_delete_company_with_active_employees()
    {
        $company = Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'email' => 'test@test.com',
            'is_active' => true,
        ]);

        // Crear empleado activo
        Employee::create([
            'company_id' => $company->id,
            'email' => 'employee@test.com',
            'password' => bcrypt('password'),
            'name' => 'Active Employee',
            'identification' => '1234567890001',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/owner/companies/' . $company->id);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'No se puede eliminar una empresa con empleados activos',
            ]);

        $this->assertDatabaseHas('companies', ['id' => $company->id]);
    }

    /** @test */
    public function owner_can_restore_deleted_company()
    {
        $company = Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Deleted Company',
            'trade_name' => 'Deleted',
            'address' => 'Address',
            'email' => 'deleted@test.com',
            'is_active' => true,
        ]);

        $company->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/owner/companies/' . $company->id . '/restore');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Empresa restaurada exitosamente',
            ]);

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function owner_can_search_companies()
    {
        Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Technology Solutions',
            'trade_name' => 'TechSol',
            'address' => 'Address',
            'email' => 'tech@test.com',
            'is_active' => true,
        ]);

        Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456002',
            'business_name' => 'Commerce Company',
            'trade_name' => 'ComCo',
            'address' => 'Address',
            'email' => 'commerce@test.com',
            'is_active' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/owner/companies?search=Technology');

        $response->assertStatus(200);
        
        $companies = $response->json('data.data');
        $this->assertCount(1, $companies);
        $this->assertEquals('Technology Solutions', $companies[0]['business_name']);
    }

    /** @test */
    public function owner_can_filter_companies_by_active_status()
    {
        Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Active Company',
            'trade_name' => 'Active',
            'address' => 'Address',
            'email' => 'active@test.com',
            'is_active' => true,
        ]);

        Company::create([
            'owner_id' => $this->owner->id,
            'ruc' => '1790123456002',
            'business_name' => 'Inactive Company',
            'trade_name' => 'Inactive',
            'address' => 'Address',
            'email' => 'inactive@test.com',
            'is_active' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/owner/companies?is_active=1');

        $response->assertStatus(200);
        
        $companies = $response->json('data.data');
        $this->assertCount(1, $companies);
        $this->assertTrue($companies[0]['is_active']);
    }
}
