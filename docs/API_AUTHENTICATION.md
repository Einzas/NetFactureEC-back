# 🔐 API Authentication - Sistema NetFacture Professional

**Versión**: 2.0.0  
**Última actualización**: Octubre 2025

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Triple Autenticación](#triple-autenticación)
3. [Superadmin Auth](#superadmin-auth)
4. [Owner Auth](#owner-auth)
5. [Employee Auth](#employee-auth)
6. [Tokens JWT](#tokens-jwt)
7. [Manejo de Errores](#manejo-de-errores)
8. [Seguridad](#seguridad)

---

## Introducción

El sistema NetFacture Professional implementa un sistema de **triple autenticación** mediante JWT (JSON Web Tokens), permitiendo diferentes niveles de acceso según el tipo de usuario.

### Tipos de Usuario

| Tipo | Descripción | Guard | Provider |
|------|-------------|-------|----------|
| **Superadmin** | Administrador global del sistema | `superadmin` | `superadmins` |
| **Owner** | Propietario de empresas | `owner` | `owners` |
| **Employee** | Empleado de una empresa | `employee` | `employees` |

---

## Triple Autenticación

### Arquitectura

```
┌─────────────────────────────────────────┐
│         JWT Authentication              │
├─────────────────────────────────────────┤
│                                         │
│  🔴 SUPERADMIN Guard                   │
│     ├── Provider: superadmins (User)   │
│     ├── TTL: 3600 segundos             │
│     └── Scope: Sistema completo        │
│                                         │
│  🟢 OWNER Guard                        │
│     ├── Provider: owners (User)        │
│     ├── TTL: 3600 segundos             │
│     └── Scope: Sus empresas            │
│                                         │
│  🔵 EMPLOYEE Guard                     │
│     ├── Provider: employees (Employee) │
│     ├── TTL: 3600 segundos             │
│     └── Scope: Su empresa + RBAC       │
│                                         │
└─────────────────────────────────────────┘
```

### Configuración (config/auth.php)

```php
'guards' => [
    'superadmin' => [
        'driver' => 'jwt',
        'provider' => 'superadmins',
    ],
    'owner' => [
        'driver' => 'jwt',
        'provider' => 'owners',
    ],
    'employee' => [
        'driver' => 'jwt',
        'provider' => 'employees',
    ],
],

'providers' => [
    'superadmins' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'owners' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'employees' => [
        'driver' => 'eloquent',
        'model' => App\Models\Employee::class,
    ],
],
```

---

## Superadmin Auth

### 1. Login

Autenticación de superadministrador del sistema.

**Endpoint**: `POST /api/v1/superadmin/login`

**Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "email": "superadmin@netfacture.ec",
  "password": "superadmin123"
}
```

**Response Success** (200):
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 1,
      "name": "Super Administrador",
      "email": "superadmin@netfacture.ec",
      "type": "superadmin",
      "created_at": "2025-10-05T12:00:00.000000Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**Response Error** (401):
```json
{
  "success": false,
  "message": "Acceso denegado. Solo superadministradores.",
  "errors": {
    "type": ["El usuario debe ser un superadministrador"]
  }
}
```

### 2. Get User Info

Obtener información del superadmin autenticado.

**Endpoint**: `GET /api/v1/superadmin/me`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Super Administrador",
    "email": "superadmin@netfacture.ec",
    "type": "superadmin",
    "created_at": "2025-10-05T12:00:00.000000Z",
    "updated_at": "2025-10-05T12:00:00.000000Z"
  }
}
```

### 3. Dashboard

Estadísticas globales del sistema.

**Endpoint**: `GET /api/v1/superadmin/dashboard`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "total_owners": 3,
    "active_owners": 3,
    "total_companies": 4,
    "active_companies": 4,
    "total_employees": 10,
    "active_employees": 10,
    "total_files": 0,
    "storage_used_mb": 0.0
  }
}
```

### 4. Refresh Token

Renovar token JWT.

**Endpoint**: `POST /api/v1/superadmin/refresh`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### 5. Logout

Cerrar sesión e invalidar token.

**Endpoint**: `POST /api/v1/superadmin/logout`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

---

## Owner Auth

### 1. Login

Autenticación de propietario de empresas.

**Endpoint**: `POST /api/v1/owner/login`

**Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "email": "juan.perez@example.com",
  "password": "password123"
}
```

**Response Success** (200):
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "user": {
      "id": 2,
      "name": "Juan Pérez",
      "email": "juan.perez@example.com",
      "type": "owner",
      "created_at": "2025-10-05T12:00:00.000000Z"
    },
    "companies": [
      {
        "id": 1,
        "ruc": "1790123456001",
        "business_name": "TECNOLOGÍA Y SOLUCIONES TEC S.A.",
        "trade_name": "TecSoluciones",
        "is_active": true,
        "employees_count": 3
      }
    ],
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**Response Error** (401):
```json
{
  "success": false,
  "message": "Acceso denegado. Solo owners.",
  "errors": {
    "type": ["El usuario debe ser un owner"]
  }
}
```

### 2. Get User Info

Obtener información del owner con sus empresas.

**Endpoint**: `GET /api/v1/owner/me`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Juan Pérez",
    "email": "juan.perez@example.com",
    "type": "owner",
    "companies": [
      {
        "id": 1,
        "ruc": "1790123456001",
        "business_name": "TECNOLOGÍA Y SOLUCIONES TEC S.A.",
        "trade_name": "TecSoluciones",
        "is_active": true,
        "employees": [
          {
            "id": 1,
            "name": "Ana Martínez",
            "email": "admin.tec@tecsoluciones.com",
            "position": "Administradora",
            "is_active": true
          }
        ]
      }
    ]
  }
}
```

### 3. Dashboard

Estadísticas de las empresas del owner.

**Endpoint**: `GET /api/v1/owner/dashboard`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "total_companies": 1,
    "active_companies": 1,
    "total_employees": 3,
    "active_employees": 3,
    "total_files": 0,
    "storage_used_mb": 0.0
  }
}
```

### 4. Refresh Token

Renovar token JWT.

**Endpoint**: `POST /api/v1/owner/refresh`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### 5. Logout

Cerrar sesión e invalidar token.

**Endpoint**: `POST /api/v1/owner/logout`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

---

## Employee Auth

### 1. Login

Autenticación de empleado de una empresa.

**Endpoint**: `POST /api/v1/employee/login`

**Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "email": "admin.tec@tecsoluciones.com",
  "password": "admin123"
}
```

**Response Success** (200):
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "employee": {
      "id": 1,
      "name": "Ana Martínez",
      "email": "admin.tec@tecsoluciones.com",
      "position": "Administradora",
      "department": "Administración",
      "is_active": true,
      "company": {
        "id": 1,
        "ruc": "1790123456001",
        "business_name": "TECNOLOGÍA Y SOLUCIONES TEC S.A.",
        "trade_name": "TecSoluciones",
        "is_active": true
      }
    },
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "display_name": "Administrador Total"
      }
    ],
    "permissions": [
      "companies.view",
      "companies.create",
      "employees.view",
      "invoices.create",
      "files.upload"
      // ... 37 permisos para rol admin
    ],
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**Response Error - Empleado Inactivo** (403):
```json
{
  "success": false,
  "message": "Empleado inactivo",
  "errors": {
    "employee": ["Tu cuenta de empleado está inactiva"]
  }
}
```

**Response Error - Empresa Inactiva** (403):
```json
{
  "success": false,
  "message": "Empresa inactiva",
  "errors": {
    "company": ["La empresa está inactiva"]
  }
}
```

### 2. Login SSO

Autenticación mediante proveedores externos (Google, Microsoft, Azure).

**Endpoint**: `POST /api/v1/employee/login/sso`

**Headers**:
```
Content-Type: application/json
```

**Request Body**:
```json
{
  "provider": "google",
  "access_token": "ya29.a0AfH6SMB..."
}
```

**Proveedores soportados**:
- `google`
- `microsoft`
- `azure`

**Response Success** (200):
```json
{
  "success": true,
  "message": "Login SSO exitoso",
  "data": {
    "employee": { /* ... */ },
    "roles": [ /* ... */ ],
    "permissions": [ /* ... */ ],
    "token": "eyJ0eXAiOiJKV1QiLC...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

**Response Error - Proveedor Inválido** (400):
```json
{
  "success": false,
  "message": "Proveedor SSO no válido",
  "errors": {
    "provider": ["Proveedores válidos: google, microsoft, azure"]
  }
}
```

### 3. Get Employee Info

Obtener información del empleado autenticado.

**Endpoint**: `GET /api/v1/employee/me`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Ana Martínez",
    "email": "admin.tec@tecsoluciones.com",
    "position": "Administradora",
    "department": "Administración",
    "is_active": true,
    "company": {
      "id": 1,
      "ruc": "1790123456001",
      "business_name": "TECNOLOGÍA Y SOLUCIONES TEC S.A.",
      "trade_name": "TecSoluciones",
      "is_active": true
    },
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "display_name": "Administrador Total"
      }
    ],
    "permissions": [
      "companies.view",
      "companies.create",
      "employees.view"
      // ... todos los permisos
    ]
  }
}
```

### 4. Check Permission

Verificar si el empleado tiene un permiso específico.

**Endpoint**: `POST /api/v1/employee/check-permission`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body**:
```json
{
  "permission": "invoices.create"
}
```

**Response - Tiene Permiso** (200):
```json
{
  "success": true,
  "data": {
    "permission": "invoices.create",
    "has_permission": true
  }
}
```

**Response - No Tiene Permiso** (200):
```json
{
  "success": true,
  "data": {
    "permission": "users.create",
    "has_permission": false
  }
}
```

### 5. Refresh Token

Renovar token JWT.

**Endpoint**: `POST /api/v1/employee/refresh`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "token_type": "bearer",
    "expires_in": 3600
  }
}
```

### 6. Logout

Cerrar sesión e invalidar token.

**Endpoint**: `POST /api/v1/employee/logout`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente"
}
```

---

## Tokens JWT

### Estructura del Token

```
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAv
YXBpL3YxL2VtcGxveWVlL2xvZ2luIiwiaWF0IjoxNzI4MTM3MDAwLCJleHAiOjE3MjgxNDA2MDAs
Im5iZiI6MTcyODEzNzAwMCwianRpIjoiWEp2WmFhU0RJWE1rOXlrViIsInN1YiI6IjEiLCJwcnYi
OiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.signature
```

### Claims del Token

#### Header
```json
{
  "typ": "JWT",
  "alg": "HS256"
}
```

#### Payload
```json
{
  "iss": "http://localhost:8000/api/v1/employee/login",
  "iat": 1728137000,
  "exp": 1728140600,
  "nbf": 1728137000,
  "jti": "XJvZaaSAIXMk9ykV",
  "sub": "1",
  "prv": "23bd5c8949f600adb39e701c400872db7a5976f7"
}
```

| Claim | Descripción |
|-------|-------------|
| `iss` | Issuer - URL que emitió el token |
| `iat` | Issued At - Timestamp de creación |
| `exp` | Expiration - Timestamp de expiración (1 hora) |
| `nbf` | Not Before - Válido desde este timestamp |
| `jti` | JWT ID - Identificador único del token |
| `sub` | Subject - ID del usuario/empleado |
| `prv` | Provider - Hash del provider usado |

### Claims Personalizados (Employee)

```json
{
  "company_id": 1,
  "name": "Ana Martínez",
  "email": "admin.tec@tecsoluciones.com",
  "position": "Administradora"
}
```

### Tiempo de Vida

```php
'ttl' => env('JWT_TTL', 60), // 60 minutos (3600 segundos)
'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // 14 días
```

### Invalidación de Tokens

Los tokens se invalidan en los siguientes casos:
- **Logout explícito**: Token agregado a blacklist
- **Expiración**: Después de 60 minutos
- **Refresh**: Token antiguo invalidado, nuevo token emitido

---

## Manejo de Errores

### Errores Comunes

#### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

**Causas**:
- Token no proporcionado
- Token inválido
- Token expirado
- Token de guard incorrecto

#### 403 Forbidden
```json
{
  "success": false,
  "message": "No autorizado",
  "errors": {
    "permission": ["No tienes permiso para realizar esta acción"]
  }
}
```

**Causas**:
- Usuario sin permisos necesarios
- Usuario sin rol requerido
- Empleado inactivo
- Empresa inactiva

#### 422 Validation Error
```json
{
  "message": "The email field is required.",
  "errors": {
    "email": ["El campo email es obligatorio"],
    "password": ["El campo password es obligatorio"]
  }
}
```

**Causas**:
- Datos de entrada inválidos
- Campos requeridos faltantes
- Formato de datos incorrecto

#### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Error interno del servidor",
  "error": "Descripción del error"
}
```

---

## Seguridad

### Mejores Prácticas

#### 1. Almacenamiento de Tokens
```javascript
// ✅ CORRECTO - localStorage o sessionStorage
localStorage.setItem('token', response.data.token);

// ❌ INCORRECTO - Cookies sin HttpOnly
document.cookie = `token=${token}`;
```

#### 2. Envío de Tokens
```bash
# ✅ CORRECTO - Header Authorization
Authorization: Bearer eyJ0eXAiOiJKV1Qi...

# ❌ INCORRECTO - Query string
GET /api/v1/employee/me?token=eyJ0eXAi...
```

#### 3. Refresh de Tokens
```javascript
// ✅ CORRECTO - Refresh antes de expiración
if (tokenExpiresInMinutes < 5) {
  await refreshToken();
}

// ❌ INCORRECTO - Esperar error 401
try {
  await api.get('/endpoint');
} catch (error) {
  if (error.status === 401) {
    await refreshToken();
  }
}
```

#### 4. Logout Seguro
```javascript
// ✅ CORRECTO - Llamar endpoint + limpiar storage
await api.post('/logout');
localStorage.removeItem('token');
router.push('/login');

// ❌ INCORRECTO - Solo limpiar storage
localStorage.removeItem('token');
```

### Configuración de Seguridad

#### Variables de Entorno
```env
JWT_SECRET=your-super-secret-key-change-this
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256
JWT_BLACKLIST_ENABLED=true
JWT_BLACKLIST_GRACE_PERIOD=30
```

#### Rate Limiting
```php
// En routes/api.php
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/superadmin/login', [SuperAdminAuthController::class, 'login']);
    Route::post('/owner/login', [OwnerAuthController::class, 'login']);
    Route::post('/employee/login', [EmployeeAuthController::class, 'login']);
});
```

**Límites recomendados**:
- Login: 10 intentos por minuto
- Refresh: 30 intentos por minuto
- Endpoints normales: 60 por minuto

---

## Ejemplos de Integración

### JavaScript/Axios

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json'
  }
});

// Interceptor para agregar token
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Interceptor para manejar errores 401
api.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Login
async function login(email, password) {
  const response = await api.post('/employee/login', { email, password });
  localStorage.setItem('token', response.data.data.token);
  return response.data;
}

// Get user info
async function getMe() {
  const response = await api.get('/employee/me');
  return response.data;
}
```

### PHP/Guzzle

```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://localhost:8000/api/v1',
    'headers' => [
        'Content-Type' => 'application/json'
    ]
]);

// Login
$response = $client->post('/employee/login', [
    'json' => [
        'email' => 'admin.tec@tecsoluciones.com',
        'password' => 'admin123'
    ]
]);

$data = json_decode($response->getBody(), true);
$token = $data['data']['token'];

// Usar token
$response = $client->get('/employee/me', [
    'headers' => [
        'Authorization' => "Bearer {$token}"
    ]
]);
```

---

## Testing

### Pruebas Manuales con cURL

```bash
# Superadmin Login
curl -X POST http://localhost:8000/api/v1/superadmin/login \
  -H "Content-Type: application/json" \
  -d '{"email":"superadmin@netfacture.ec","password":"superadmin123"}'

# Owner Login
curl -X POST http://localhost:8000/api/v1/owner/login \
  -H "Content-Type: application/json" \
  -d '{"email":"juan.perez@example.com","password":"password123"}'

# Employee Login
curl -X POST http://localhost:8000/api/v1/employee/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin.tec@tecsoluciones.com","password":"admin123"}'

# Usar token
TOKEN="eyJ0eXAi..."
curl -X GET http://localhost:8000/api/v1/employee/me \
  -H "Authorization: Bearer $TOKEN"
```

---

## FAQ

### ¿Cuánto tiempo dura un token?
60 minutos (3600 segundos). Usa el endpoint `/refresh` antes de que expire.

### ¿Puedo tener múltiples tokens activos?
Sí, cada login genera un token nuevo. Todos son válidos hasta expirar o hacer logout.

### ¿Qué pasa si un empleado es desactivado?
En el próximo request con su token, recibirá error 403. El token sigue técnicamente válido pero la middleware rechaza el acceso.

### ¿Cómo rotar el JWT_SECRET sin invalidar todos los tokens?
No es posible. Al cambiar JWT_SECRET todos los tokens existentes se invalidan. Avisa a los usuarios con anticipación.

### ¿Puedo usar el mismo token en múltiples guards?
No. Un token de `employee` solo funciona en rutas con `auth:employee`. No sirve para `auth:owner`.

---

**Última actualización**: 5 de Octubre, 2025  
**Mantenido por**: Equipo NetFacture  
**Contacto**: dev@netfacture.ec
