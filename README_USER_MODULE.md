# 📚 Módulo de Gestión de Usuarios - NetFactureEC API

## 📋 Descripción

Módulo profesional de autenticación y gestión de usuarios para la API REST de NetFactureEC. Implementa autenticación JWT, gestión de roles y permisos con Laravel Spatie Permission, y medidas de seguridad actualizadas.

## 🔐 Características de Seguridad

### Autenticación JWT (JSON Web Tokens)
- Tokens seguros con firma HMAC-SHA256
- Expiración configurable de tokens
- Refresh tokens para renovar sesiones
- Protección contra ataques de replay

### Encriptación de Contraseñas
- **BCrypt** con factor de coste configurable
- Hash automático mediante Laravel Hashing
- Validación de complejidad de contraseñas:
  - Mínimo 8 caracteres
  - Mayúsculas y minúsculas
  - Números
  - Símbolos especiales

### Medidas de Seguridad Adicionales
- **Soft Deletes**: Los usuarios eliminados se marcan como inactivos
- **Rate Limiting**: Protección contra fuerza bruta (configurar en routes)
- **CORS**: Control de acceso desde diferentes dominios
- **Validación de entrada**: Sanitización y validación de todos los datos
- **Middleware de autorización**: Verificación de roles y permisos
- **Registro de actividad**: Tracking de último login e IP
- **Usuarios activos/inactivos**: Control de acceso mediante flag `is_active`

## 🏗️ Arquitectura del Módulo

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php         # Autenticación (login, register, logout)
│   │   ├── UserController.php         # CRUD de usuarios
│   │   ├── RoleController.php         # Gestión de roles
│   │   └── PermissionController.php   # Gestión de permisos
│   ├── Middleware/
│   │   ├── JwtMiddleware.php          # Validación de tokens JWT
│   │   ├── CheckRole.php              # Verificación de roles
│   │   └── CheckPermission.php        # Verificación de permisos
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php       # Validación de login
│   │   │   └── RegisterRequest.php    # Validación de registro
│   │   ├── User/
│   │   │   ├── StoreUserRequest.php   # Validación crear usuario
│   │   │   └── UpdateUserRequest.php  # Validación actualizar usuario
│   │   └── Role/
│   │       ├── StoreRoleRequest.php   # Validación crear rol
│   │       └── UpdateRoleRequest.php  # Validación actualizar rol
│   └── Resources/
│       ├── UserResource.php           # Transformación de datos de usuario
│       ├── RoleResource.php           # Transformación de datos de rol
│       └── PermissionResource.php     # Transformación de datos de permiso
├── Models/
│   └── User.php                       # Modelo de usuario con traits JWT y Roles
database/
├── migrations/
│   ├── 2025_10_01_050244_create_permission_tables.php
│   └── 2025_10_01_050325_add_fields_to_users_table.php
└── seeders/
    ├── RolePermissionSeeder.php       # Seeder de roles y permisos
    └── DatabaseSeeder.php             # Seeder principal
```

## 📊 Estructura de Base de Datos

### Tabla: users
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único del usuario |
| name | string(255) | Nombre completo |
| email | string(255) | Email único |
| password | string | Contraseña hasheada |
| phone | string(20) | Teléfono (opcional) |
| is_active | boolean | Estado del usuario |
| last_login_at | timestamp | Último acceso |
| last_login_ip | string(45) | IP del último acceso |
| email_verified_at | timestamp | Verificación de email |
| remember_token | string | Token de sesión |
| deleted_at | timestamp | Soft delete |
| created_at | timestamp | Fecha de creación |
| updated_at | timestamp | Fecha de actualización |

### Tablas de Spatie Permission
- **roles**: Roles del sistema
- **permissions**: Permisos disponibles
- **model_has_permissions**: Relación usuario-permisos
- **model_has_roles**: Relación usuario-roles
- **role_has_permissions**: Relación rol-permisos

## 🎭 Sistema de Roles y Permisos

### Roles Predefinidos

#### 1. **superadmin**
- Acceso total al sistema
- Puede gestionar usuarios, roles y permisos
- No puede ser eliminado

#### 2. **admin**
- Gestión de usuarios
- Visualización de roles y permisos
- Acceso a reportes

#### 3. **user**
- Usuario estándar
- Acceso básico al sistema
- Sin permisos administrativos

### Permisos Disponibles

#### Gestión de Usuarios
- `users.view` - Ver usuarios
- `users.create` - Crear usuarios
- `users.edit` - Editar usuarios
- `users.delete` - Eliminar usuarios

#### Gestión de Roles
- `roles.view` - Ver roles
- `roles.create` - Crear roles
- `roles.edit` - Editar roles
- `roles.delete` - Eliminar roles

#### Gestión de Permisos
- `permissions.view` - Ver permisos
- `permissions.create` - Crear permisos
- `permissions.edit` - Editar permisos
- `permissions.delete` - Eliminar permisos

## 🔌 API Endpoints

### Base URL: `/api/v1`

### 🔓 Endpoints Públicos

#### Registro de Usuario
```http
POST /api/v1/register
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "phone": "+52123456789"
}
```

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "phone": "+52123456789",
      "is_active": true,
      "roles": [{"id": 3, "name": "user"}]
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

#### Login
```http
POST /api/v1/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "Password123!"
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com",
      "is_active": true,
      "last_login_at": "2025-10-01 10:30:00",
      "roles": [{"id": 3, "name": "user"}],
      "permissions": []
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### 🔒 Endpoints Protegidos (Requieren JWT)

**Header requerido:**
```http
Authorization: Bearer {token}
```

#### Obtener Usuario Actual
```http
GET /api/v1/me
```

#### Cerrar Sesión
```http
POST /api/v1/logout
```

#### Refrescar Token
```http
POST /api/v1/refresh
```

### 👥 Gestión de Usuarios (Admin/Superadmin)

#### Listar Usuarios
```http
GET /api/v1/users?page=1&per_page=15&search=juan
```

#### Crear Usuario
```http
POST /api/v1/users
Content-Type: application/json

{
  "name": "María López",
  "email": "maria@example.com",
  "password": "Password123!",
  "phone": "+52987654321",
  "is_active": true,
  "roles": ["admin"]
}
```

#### Ver Usuario
```http
GET /api/v1/users/{id}
```

#### Actualizar Usuario
```http
PUT /api/v1/users/{id}
Content-Type: application/json

{
  "name": "María López Updated",
  "is_active": false,
  "roles": ["user"]
}
```

#### Eliminar Usuario
```http
DELETE /api/v1/users/{id}
```

### 🎭 Gestión de Roles

#### Listar Roles
```http
GET /api/v1/roles
```

#### Crear Rol
```http
POST /api/v1/roles
Content-Type: application/json

{
  "name": "moderator",
  "permissions": ["users.view", "users.edit"]
}
```

#### Ver Rol
```http
GET /api/v1/roles/{id}
```

#### Actualizar Rol
```http
PUT /api/v1/roles/{id}
Content-Type: application/json

{
  "name": "moderator",
  "permissions": ["users.view", "users.edit", "users.delete"]
}
```

#### Eliminar Rol
```http
DELETE /api/v1/roles/{id}
```

### 🔑 Gestión de Permisos

Similar a roles, endpoints: `/api/v1/permissions`

## 🚀 Instalación y Configuración

### 1. Instalar Dependencias
```bash
composer install
```

### 2. Configurar Variables de Entorno
```env
# JWT Configuration
JWT_SECRET=your-secret-key
JWT_TTL=60  # Token expiration in minutes
JWT_REFRESH_TTL=20160  # Refresh token expiration (2 weeks)

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=NetFactureEC
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Ejecutar Migraciones
```bash
php artisan migrate
```

### 4. Ejecutar Seeders
```bash
php artisan db:seed
```

**Usuarios de prueba creados:**
- **Superadmin**: superadmin@NetFactureEC.com / Password123!
- **Admin**: admin@NetFactureEC.com / Password123!
- **User**: user@NetFactureEC.com / Password123!

### 5. Limpiar Caché de Permisos
```bash
php artisan permission:cache-reset
```

## 🛡️ Uso de Middleware

### En Rutas
```php
// Requiere autenticación JWT
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
});

// Requiere rol específico
Route::middleware(['jwt.auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
});

// Requiere permiso específico
Route::middleware(['jwt.auth', 'permission:users.create'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});
```

### En Controladores
```php
public function __construct()
{
    $this->middleware('jwt.auth');
    $this->middleware('role:admin')->only(['destroy']);
    $this->middleware('permission:users.edit')->only(['update']);
}
```

### Verificación Manual
```php
// Verificar rol
if (auth()->user()->hasRole('admin')) {
    // Código para admin
}

// Verificar permiso
if (auth()->user()->can('users.create')) {
    // Código para crear usuario
}

// Verificar múltiples roles
if (auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
    // Código para admin o superadmin
}
```

## 📝 Códigos de Respuesta HTTP

| Código | Significado | Uso |
|--------|-------------|-----|
| 200 | OK | Petición exitosa |
| 201 | Created | Recurso creado exitosamente |
| 400 | Bad Request | Datos inválidos |
| 401 | Unauthorized | No autenticado o token inválido |
| 403 | Forbidden | No tiene permisos |
| 404 | Not Found | Recurso no encontrado |
| 422 | Unprocessable Entity | Errores de validación |
| 500 | Internal Server Error | Error del servidor |

## 🔍 Ejemplos de Uso

### Ejemplo con cURL
```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@NetFactureEC.com","password":"Password123!"}'

# Obtener usuario actual (con token)
curl -X GET http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Ejemplo con JavaScript (Axios)
```javascript
// Login
const login = async () => {
  const response = await axios.post('/api/v1/login', {
    email: 'admin@NetFactureEC.com',
    password: 'Password123!'
  });
  
  const token = response.data.data.token;
  localStorage.setItem('token', token);
};

// Request con token
const getUsers = async () => {
  const token = localStorage.getItem('token');
  const response = await axios.get('/api/v1/users', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return response.data;
};
```

## 🐛 Troubleshooting

### Token expirado
**Solución**: Usar el endpoint `/api/v1/refresh` para obtener un nuevo token.

### Error "Unauthenticated"
**Solución**: Verificar que el header `Authorization: Bearer {token}` esté presente.

### Error de permisos
**Solución**: Verificar que el usuario tenga el rol o permiso necesario.

### Errores de CORS
**Solución**: Configurar CORS en `config/cors.php`.

## 📚 Recursos Adicionales

- [Documentación de JWT-Auth](https://jwt-auth.readthedocs.io/)
- [Documentación de Spatie Permission](https://spatie.be/docs/laravel-permission/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)

## 🔄 Actualizaciones Futuras

- [ ] Autenticación de dos factores (2FA)
- [ ] OAuth2 / Social Login
- [ ] Logs de auditoría detallados
- [ ] Recuperación de contraseña
- [ ] Verificación de email
- [ ] Bloqueo de cuenta tras intentos fallidos
- [ ] Historial de cambios de contraseña

## 📞 Soporte

Para preguntas o problemas, contactar al equipo de desarrollo.

---
## 🛠️ Guía de Contribución
### Commit Messages
```
feat: nueva funcionalidad
fix: corrección de bug
docs: actualización de documentación
style: cambios de formato
refactor: refactorización de código
test: adición o modificación de tests
chore: tareas de mantenimiento
```

**Versión**: 1.0.0  
**Última actualización**: Octubre 2025  
**Desarrollado para**: NetFactureEC API REST
