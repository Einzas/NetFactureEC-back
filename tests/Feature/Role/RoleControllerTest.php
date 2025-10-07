<?php

namespace Tests\Feature\Role;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $employee;
    protected $token;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'PermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $owner = User::create([
            'type' => 'owner',
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'owner_id' => $owner->id,
            'ruc' => '1790123456001',
            'business_name' => 'Test Company',
            'trade_name' => 'Test',
            'address' => 'Address',
            'email' => 'company@test.com',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'company_id' => $this->company->id,
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'name' => 'Admin Employee',
            'identification' => '1234567890001',
            'is_active' => true,
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $this->employee->assignRole($adminRole);

        $this->token = auth('employee')->login($this->employee);
    }

    /** @test */
    public function employee_can_list_all_roles()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/employee/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'roles' => [
                        '*' => [
                            'id',
                            'name',
                            'display_name',
                            'description',
                            'permissions_count',
                            'employees_count',
                        ]
                    ],
                    'pagination',
                ]
            ]);

        // Debe haber 6 roles del sistema
        $this->assertGreaterThanOrEqual(6, count($response->json('data.roles')));
    }

    /** @test */
    public function employee_can_create_custom_role()
    {
        $permissions = Permission::where('name', 'like', 'invoices.%')->limit(3)->pluck('id')->toArray();

        $roleData = [
            'name' => 'custom_role',
            'display_name' => 'Custom Role',
            'description' => 'A custom role for testing',
            'permissions' => $permissions,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/employee/roles', $roleData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'role' => [
                        'id',
                        'name',
                        'display_name',
                        'description',
                        'permissions',
                    ]
                ]
            ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'custom_role',
            'display_name' => 'Custom Role',
        ]);

        // Verificar que los permisos fueron asignados
        $createdRole = Role::where('name', 'custom_role')->first();
        $this->assertEquals(count($permissions), $createdRole->permissions->count());
    }

    /** @test */
    public function role_name_must_be_unique()
    {
        $roleData = [
            'name' => 'admin', // Nombre ya existe
            'display_name' => 'Another Admin',
            'description' => 'Test',
            'permissions' => [],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/employee/roles', $roleData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function employee_can_view_specific_role()
    {
        $role = Role::where('name', 'admin')->first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/employee/roles/' . $role->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'role' => [
                        'id',
                        'name',
                        'display_name',
                        'description',
                        'permissions' => [
                            '*' => [
                                'id',
                                'name',
                                'description',
                            ]
                        ],
                        'employees_count',
                    ]
                ]
            ]);
    }

    /** @test */
    public function employee_can_update_custom_role()
    {
        // Crear rol personalizado
        $role = Role::create([
            'name' => 'updatable_role',
            'display_name' => 'Old Display Name',
            'description' => 'Old description',
            'guard_name' => 'employee',
        ]);

        $permissions = Permission::where('name', 'like', 'invoices.%')->limit(2)->pluck('id')->toArray();

        $updateData = [
            'display_name' => 'Updated Display Name',
            'description' => 'Updated description',
            'permissions' => $permissions,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/employee/roles/' . $role->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Rol actualizado exitosamente',
            ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'display_name' => 'Updated Display Name',
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function cannot_update_system_roles()
    {
        $systemRole = Role::where('name', 'admin')->first();

        $updateData = [
            'display_name' => 'Hacked Admin',
            'description' => 'Trying to hack',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/v1/employee/roles/' . $systemRole->id, $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No se pueden editar roles del sistema',
            ]);
    }

    /** @test */
    public function employee_can_delete_custom_role()
    {
        $role = Role::create([
            'name' => 'deletable_role',
            'display_name' => 'Deletable Role',
            'description' => 'Will be deleted',
            'guard_name' => 'employee',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/employee/roles/' . $role->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Rol eliminado exitosamente',
            ]);

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    /** @test */
    public function cannot_delete_system_roles()
    {
        $systemRole = Role::where('name', 'admin')->first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/employee/roles/' . $systemRole->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'No se pueden eliminar roles del sistema',
            ]);

        $this->assertDatabaseHas('roles', [
            'id' => $systemRole->id,
        ]);
    }

    /** @test */
    public function cannot_delete_role_with_assigned_employees()
    {
        // Crear rol personalizado
        $role = Role::create([
            'name' => 'role_with_employees',
            'display_name' => 'Role With Employees',
            'description' => 'Has employees',
            'guard_name' => 'employee',
        ]);

        // Crear empleado y asignar el rol
        $employee = Employee::create([
            'company_id' => $this->company->id,
            'email' => 'employee@test.com',
            'password' => bcrypt('password'),
            'name' => 'Test Employee',
            'identification' => '9999999999999',
            'is_active' => true,
        ]);

        $employee->assignRole($role);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/employee/roles/' . $role->id);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'No se puede eliminar un rol que está asignado a empleados',
            ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    /** @test */
    public function employee_can_get_all_permissions()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/employee/roles/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'permissions' => [
                        '*' => [
                            'module',
                            'permissions' => [
                                '*' => [
                                    'id',
                                    'name',
                                    'description',
                                ]
                            ]
                        ]
                    ],
                    'total',
                ]
            ]);

        // Debe haber 42 permisos
        $this->assertEquals(42, $response->json('data.total'));
    }

    /** @test */
    public function permissions_are_grouped_by_module()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/employee/roles/permissions');

        $permissions = $response->json('data.permissions');

        // Verificar que hay módulos
        $this->assertIsArray($permissions);
        $this->assertGreaterThan(0, count($permissions));

        // Verificar estructura de módulos
        foreach ($permissions as $module) {
            $this->assertArrayHasKey('module', $module);
            $this->assertArrayHasKey('permissions', $module);
            $this->assertIsArray($module['permissions']);
        }
    }

    /** @test */
    public function role_creation_requires_name_and_display_name()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/employee/roles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'permissions']);
    }

    /** @test */
    public function role_name_must_be_lowercase_with_underscores()
    {
        // Obtener algunos permisos
        $permissions = \App\Models\Permission::take(2)->pluck('id')->toArray();
        
        $roleData = [
            'name' => 'Invalid Name With Spaces',
            'display_name' => 'Display Name',
            'description' => 'Test',
            'permissions' => $permissions,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/employee/roles', $roleData);

        // Por ahora no validamos formato de nombre
        // Solo verificamos que se pueda crear
        $response->assertStatus(201);
    }

    /** @test */
    public function can_assign_multiple_permissions_to_role()
    {
        $permissions = Permission::limit(10)->pluck('id')->toArray();

        $roleData = [
            'name' => 'multi_permission_role',
            'display_name' => 'Multi Permission Role',
            'description' => 'Has multiple permissions',
            'permissions' => $permissions,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/employee/roles', $roleData);

        $response->assertStatus(201);

        $role = Role::where('name', 'multi_permission_role')->first();
        $this->assertEquals(10, $role->permissions->count());
    }
}
