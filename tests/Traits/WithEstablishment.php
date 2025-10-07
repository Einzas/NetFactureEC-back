<?php

namespace Tests\Traits;

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

trait WithEstablishment
{
    protected Establishment $establishment;
    protected User $admin;
    protected User $user;
    protected User $superadmin;

    /**
     * Setup establishment with users for testing
     */
    protected function setupEstablishment(): void
    {
        // Crear roles si no existen
        $this->createRoles();

        // Crear establecimiento de prueba
        $this->establishment = Establishment::factory()->create([
            'ruc' => '1790123456001',
            'business_name' => 'TEST CORPORATION S.A.',
            'trade_name' => 'Test Corp',
            'establishment_code' => '001',
            'emission_point' => '001',
            'environment' => 'pruebas',
            'is_active' => true,
        ]);

        // Crear admin del establecimiento
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('Password123!'),
            'establishment_id' => $this->establishment->id,
            'is_establishment_admin' => true,
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin');

        // Crear usuario regular del establecimiento
        $this->user = User::factory()->create([
            'email' => 'user@test.com',
            'password' => Hash::make('Password123!'),
            'establishment_id' => $this->establishment->id,
            'is_establishment_admin' => false,
            'is_active' => true,
        ]);
        $this->user->assignRole('user');

        // Crear superadmin (sin establecimiento)
        $this->superadmin = User::factory()->create([
            'email' => 'superadmin@test.com',
            'password' => Hash::make('SuperAdmin123!'),
            'establishment_id' => null,
            'is_establishment_admin' => false,
            'is_active' => true,
        ]);
        $this->superadmin->assignRole('superadmin');
    }

    /**
     * Create roles if they don't exist
     */
    protected function createRoles(): void
    {
        $guards = ['api', 'web']; // Crear roles para ambos guards
        $roles = ['superadmin', 'admin', 'user'];

        foreach ($guards as $guard) {
            foreach ($roles as $roleName) {
                if (!Role::where('name', $roleName)->where('guard_name', $guard)->exists()) {
                    Role::create(['name' => $roleName, 'guard_name' => $guard]);
                }
            }
        }
    }

    /**
     * Get authenticated token for a user
     */
    protected function getAuthToken(User $user): string
    {
        return auth('api')->login($user);
    }

    /**
     * Get authorization header with token
     */
    protected function authHeaders(User $user): array
    {
        $token = $this->getAuthToken($user);
        return ['Authorization' => "Bearer {$token}"];
    }

    /**
     * Create a second establishment for multi-tenant testing
     */
    protected function createSecondEstablishment(): array
    {
        $establishment2 = Establishment::factory()->create([
            'ruc' => '0992345678001',
            'business_name' => 'SECOND CORP S.A.',
        ]);

        $admin2 = User::factory()->create([
            'email' => 'admin2@test.com',
            'password' => Hash::make('Password123!'),
            'establishment_id' => $establishment2->id,
            'is_establishment_admin' => true,
            'is_active' => true,
        ]);
        $admin2->assignRole('admin');

        return [
            'establishment' => $establishment2,
            'admin' => $admin2,
        ];
    }
}
