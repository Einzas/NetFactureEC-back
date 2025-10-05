<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth; // Importar JWTAuth para generar tokens

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    /**
     * Configuración inicial para cada test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Ejecuta los seeders, incluyendo roles y permisos
        $this->seed();

        // Crear un usuario de prueba y asignarle el rol de admin
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Generar token JWT para el usuario
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Helper: Devuelve los headers con el token JWT
     */
    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ];
    }

    /** @test */
    public function puede_listar_categorias()
    {
        // Crear 3 categorías de prueba usando factory
        Category::factory()->count(3)->create();

        // Realizar petición GET a la API con JWT
        $response = $this->getJson('/api/v1/categories', $this->headers());

        // Verificar que el status sea 200 y que devuelva un array 'data'
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function puede_crear_una_categoria()
    {
        // Datos de la nueva categoría a crear
        $data = [
            'name' => 'Electrónica',
            'description' => 'Productos electrónicos',
            'status' => 'active'
        ];

        // Realizar petición POST a la API con JWT
        $response = $this->postJson('/api/v1/categories', $data, $this->headers());

        // Verificar que el status sea 201 y que el nombre se haya guardado correctamente
        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Electrónica']);
    }

    /** @test */
    public function puede_ver_una_categoria()
    {
        // Crear una categoría de prueba
        $category = Category::factory()->create();

        // Petición GET para ver los detalles de la categoría
        $response = $this->getJson("/api/v1/categories/{$category->id}", $this->headers());

        // Verificar que devuelva status 200 y que coincida el ID
        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $category->id]);
    }

    /** @test */
    public function puede_actualizar_una_categoria()
    {
        // Crear categoría de prueba
        $category = Category::factory()->create();

        // Datos para actualizar
        $data = [
            'name' => 'Electrodomésticos',
            'description' => 'Lavadora',
            'status' => 'active', // o el valor que corresponda según tu app
        ];

        // Petición PUT con JWT para actualizar
        $response = $this->putJson("/api/v1/categories/{$category->id}", $data, $this->headers());

        // Verificar status y mensaje de confirmación
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
        ])
        ->assertJsonFragment(['name' => 'Electrodomésticos']);
    }

    /** @test */
    public function puede_eliminar_una_categoria()
    {
        // Crear categoría de prueba
        $category = Category::factory()->create();

        // Petición DELETE con JWT
        $response = $this->deleteJson("/api/v1/categories/{$category->id}", [], $this->headers());

        // Verificar status y mensaje de confirmación
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente',
            ]);

        // Verificar que la categoría ya no exista en la base de datos
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
