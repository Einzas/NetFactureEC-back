# 🔴 API Superadmin - Sistema NetFacture Professional

**Versión**: 2.0.0  
**Última actualización**: Octubre 2025

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Autenticación](#autenticación)
3. [Dashboard y Analytics](#dashboard-y-analytics)
4. [Gestión de Owners](#gestión-de-owners)
5. [Gestión de Empresas](#gestión-de-empresas)
6. [Configuración Global](#configuración-global)
7. [Logs y Auditoría](#logs-y-auditoría)

---

## Introducción

El módulo Superadmin permite al administrador global del sistema gestionar owners, empresas, configuración global y monitorear el estado del sistema completo.

### Alcance del Superadmin

✅ **Puede hacer**:
- Ver y gestionar todos los owners
- Ver y gestionar todas las empresas
- Ver analytics globales del sistema
- Configurar parámetros globales
- Ver logs y auditoría completa
- Gestionar permisos del sistema (users.*)

❌ **No puede hacer**:
- Acceder a datos privados de facturas
- Ver archivos de las empresas
- Realizar operaciones de negocio de empresas

---

## Autenticación

### Login

**Endpoint**: `POST /api/v1/superadmin/login`

**Request**:
```json
{
  "email": "superadmin@netfacture.ec",
  "password": "superadmin123"
}
```

**Response** (200):
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

### Get Profile

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

### Refresh Token

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

### Logout

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

## Dashboard y Analytics

### Obtener Dashboard Global

Estadísticas completas del sistema.

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
    "users": {
      "total_owners": 3,
      "active_owners": 3,
      "inactive_owners": 0,
      "owners_created_this_month": 1
    },
    "companies": {
      "total_companies": 4,
      "active_companies": 4,
      "inactive_companies": 0,
      "production_companies": 2,
      "test_companies": 2,
      "companies_created_this_month": 2
    },
    "employees": {
      "total_employees": 10,
      "active_employees": 10,
      "inactive_employees": 0,
      "employees_by_role": {
        "admin": 4,
        "contador": 2,
        "facturador": 2,
        "vendedor": 1,
        "auditor": 1
      }
    },
    "storage": {
      "total_files": 0,
      "storage_used_mb": 0.0,
      "storage_limit_mb": 102400.0,
      "storage_percentage": 0.0
    },
    "activity": {
      "logins_today": 15,
      "invoices_created_today": 5,
      "files_uploaded_today": 2
    }
  }
}
```

### Analytics por Período

Estadísticas por rango de fechas.

**Endpoint**: `GET /api/v1/superadmin/analytics`

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
```
?start_date=2025-01-01&end_date=2025-10-05
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "period": {
      "start_date": "2025-01-01",
      "end_date": "2025-10-05"
    },
    "new_owners": 3,
    "new_companies": 4,
    "new_employees": 10,
    "total_logins": 450,
    "growth": {
      "owners_percentage": 100.0,
      "companies_percentage": 100.0,
      "employees_percentage": 100.0
    }
  }
}
```

---

## Gestión de Owners

### Listar Owners

**Endpoint**: `GET /api/v1/superadmin/owners`

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
```
?page=1&per_page=15&search=juan&status=active
```

**Filtros disponibles**:
- `search`: Buscar por nombre o email
- `status`: `active`, `inactive`, `all`
- `page`: Número de página
- `per_page`: Resultados por página (default: 15, max: 100)

**Response** (200):
```json
{
  "success": true,
  "data": {
    "owners": [
      {
        "id": 2,
        "name": "Juan Pérez",
        "email": "juan.perez@example.com",
        "type": "owner",
        "is_active": true,
        "created_at": "2025-10-05T12:00:00.000000Z",
        "companies_count": 1,
        "employees_count": 3,
        "storage_used_mb": 0.0
      },
      {
        "id": 3,
        "name": "María González",
        "email": "maria.gonzalez@example.com",
        "type": "owner",
        "is_active": true,
        "created_at": "2025-10-05T12:00:00.000000Z",
        "companies_count": 2,
        "employees_count": 3,
        "storage_used_mb": 0.0
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 3,
      "last_page": 1
    }
  }
}
```

### Ver Owner Específico

**Endpoint**: `GET /api/v1/superadmin/owners/{id}`

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
    "is_active": true,
    "created_at": "2025-10-05T12:00:00.000000Z",
    "updated_at": "2025-10-05T12:00:00.000000Z",
    "companies": [
      {
        "id": 1,
        "ruc": "1790123456001",
        "business_name": "TECNOLOGÍA Y SOLUCIONES TEC S.A.",
        "trade_name": "TecSoluciones",
        "is_active": true,
        "employees_count": 3,
        "storage_used_mb": 0.0
      }
    ],
    "statistics": {
      "total_companies": 1,
      "active_companies": 1,
      "total_employees": 3,
      "active_employees": 3,
      "total_storage_mb": 0.0
    }
  }
}
```

### Crear Owner

**Endpoint**: `POST /api/v1/superadmin/owners`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request**:
```json
{
  "name": "Carlos Mendoza",
  "email": "carlos.mendoza@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response** (201):
```json
{
  "success": true,
  "message": "Owner creado exitosamente",
  "data": {
    "id": 5,
    "name": "Carlos Mendoza",
    "email": "carlos.mendoza@example.com",
    "type": "owner",
    "is_active": true,
    "created_at": "2025-10-05T15:30:00.000000Z"
  }
}
```

### Actualizar Owner

**Endpoint**: `PUT /api/v1/superadmin/owners/{id}`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request**:
```json
{
  "name": "Carlos Alberto Mendoza",
  "email": "carlos.mendoza@example.com",
  "is_active": true
}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Owner actualizado exitosamente",
  "data": {
    "id": 5,
    "name": "Carlos Alberto Mendoza",
    "email": "carlos.mendoza@example.com",
    "type": "owner",
    "is_active": true,
    "updated_at": "2025-10-05T15:35:00.000000Z"
  }
}
```

### Activar/Desactivar Owner

**Endpoint**: `PATCH /api/v1/superadmin/owners/{id}/toggle-status`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Estado del owner actualizado",
  "data": {
    "id": 5,
    "is_active": false,
    "updated_at": "2025-10-05T15:40:00.000000Z"
  }
}
```

### Eliminar Owner

**Endpoint**: `DELETE /api/v1/superadmin/owners/{id}`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Owner eliminado exitosamente"
}
```

**Nota**: Esto es soft delete. El owner puede ser restaurado.

---

## Gestión de Empresas

### Listar Todas las Empresas

**Endpoint**: `GET /api/v1/superadmin/companies`

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
```
?page=1&per_page=15&search=TecSoluciones&status=active&environment=production
```

**Filtros disponibles**:
- `search`: Buscar por RUC, razón social, nombre comercial
- `status`: `active`, `inactive`, `all`
- `environment`: `production`, `test`, `all`
- `owner_id`: ID del owner
- `page`: Número de página
- `per_page`: Resultados por página

**Response** (200):
```json
{
  "success": true,
  "data": {
    "companies": [
      {
        "id": 1,
        "ruc": "1790123456001",
        "business_name": "TECNOLOGÍA Y SOLUCIONES TEC S.A.",
        "trade_name": "TecSoluciones",
        "environment": "production",
        "is_active": true,
        "owner": {
          "id": 2,
          "name": "Juan Pérez",
          "email": "juan.perez@example.com"
        },
        "employees_count": 3,
        "storage_used_mb": 0.0,
        "storage_limit_mb": 1024.0,
        "employees_limit": 10,
        "created_at": "2025-10-05T12:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 4,
      "last_page": 1
    }
  }
}
```

### Ver Empresa Específica

**Endpoint**: `GET /api/v1/superadmin/companies/{id}`

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
    "ruc": "1790123456001",
    "business_name": "TECNOLOGÍA Y SOLUCIONES TEC S.A.",
    "trade_name": "TecSoluciones",
    "email": "contacto@tecsoluciones.com",
    "phone": "0987654321",
    "address": "Av. Principal 123",
    "environment": "production",
    "is_active": true,
    "storage_used_mb": 0.0,
    "storage_limit_mb": 1024.0,
    "employees_limit": 10,
    "subscription_start": "2025-10-05",
    "subscription_end": null,
    "owner": {
      "id": 2,
      "name": "Juan Pérez",
      "email": "juan.perez@example.com"
    },
    "employees": [
      {
        "id": 1,
        "name": "Ana Martínez",
        "email": "admin.tec@tecsoluciones.com",
        "position": "Administradora",
        "is_active": true
      }
    ],
    "sri_config": {
      "has_certificate": false,
      "certificate_expires_at": null
    },
    "created_at": "2025-10-05T12:00:00.000000Z",
    "updated_at": "2025-10-05T12:00:00.000000Z"
  }
}
```

### Actualizar Límites de Empresa

**Endpoint**: `PATCH /api/v1/superadmin/companies/{id}/limits`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request**:
```json
{
  "employees_limit": 20,
  "storage_limit_mb": 2048
}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Límites actualizados exitosamente",
  "data": {
    "id": 1,
    "employees_limit": 20,
    "storage_limit_mb": 2048,
    "updated_at": "2025-10-05T16:00:00.000000Z"
  }
}
```

### Activar/Desactivar Empresa

**Endpoint**: `PATCH /api/v1/superadmin/companies/{id}/toggle-status`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Estado de la empresa actualizado",
  "data": {
    "id": 1,
    "is_active": false,
    "updated_at": "2025-10-05T16:05:00.000000Z"
  }
}
```

---

## Configuración Global

### Obtener Configuración

**Endpoint**: `GET /api/v1/superadmin/settings`

**Headers**:
```
Authorization: Bearer {token}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "system": {
      "maintenance_mode": false,
      "registration_enabled": true,
      "default_storage_limit_mb": 1024,
      "default_employees_limit": 10,
      "max_file_size_mb": 50
    },
    "sri": {
      "test_environment_enabled": true,
      "production_environment_enabled": true
    },
    "notifications": {
      "email_enabled": true,
      "storage_warning_percentage": 80,
      "certificate_expiry_warning_days": 30
    }
  }
}
```

### Actualizar Configuración

**Endpoint**: `PUT /api/v1/superadmin/settings`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request**:
```json
{
  "maintenance_mode": false,
  "registration_enabled": true,
  "default_storage_limit_mb": 2048,
  "default_employees_limit": 15,
  "max_file_size_mb": 100
}
```

**Response** (200):
```json
{
  "success": true,
  "message": "Configuración actualizada exitosamente",
  "data": {
    "maintenance_mode": false,
    "registration_enabled": true,
    "default_storage_limit_mb": 2048,
    "default_employees_limit": 15,
    "max_file_size_mb": 100,
    "updated_at": "2025-10-05T16:15:00.000000Z"
  }
}
```

---

## Logs y Auditoría

### Ver Logs del Sistema

**Endpoint**: `GET /api/v1/superadmin/logs`

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
```
?page=1&per_page=50&level=error&date=2025-10-05
```

**Filtros disponibles**:
- `level`: `debug`, `info`, `warning`, `error`, `critical`
- `date`: Fecha específica (YYYY-MM-DD)
- `user_id`: ID del usuario
- `action`: Tipo de acción
- `page`: Número de página
- `per_page`: Resultados por página

**Response** (200):
```json
{
  "success": true,
  "data": {
    "logs": [
      {
        "id": 1,
        "level": "info",
        "message": "Owner login successful",
        "user_id": 2,
        "user_name": "Juan Pérez",
        "action": "login",
        "ip_address": "192.168.1.100",
        "user_agent": "Mozilla/5.0...",
        "created_at": "2025-10-05T14:30:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 50,
      "total": 150,
      "last_page": 3
    }
  }
}
```

### Exportar Logs

**Endpoint**: `GET /api/v1/superadmin/logs/export`

**Headers**:
```
Authorization: Bearer {token}
```

**Query Parameters**:
```
?start_date=2025-10-01&end_date=2025-10-05&format=csv
```

**Formatos disponibles**:
- `csv`
- `json`
- `xlsx`

**Response** (200):
Descarga archivo con los logs filtrados.

---

## Códigos de Estado

| Código | Significado |
|--------|-------------|
| 200 | OK - Operación exitosa |
| 201 | Created - Recurso creado |
| 400 | Bad Request - Datos inválidos |
| 401 | Unauthorized - No autenticado |
| 403 | Forbidden - No es superadmin |
| 404 | Not Found - Recurso no encontrado |
| 422 | Validation Error - Errores de validación |
| 500 | Internal Server Error - Error del servidor |

---

## Ejemplos de Uso

### Crear Owner y Asignar Empresa

```bash
# 1. Crear owner
curl -X POST http://localhost:8000/api/v1/superadmin/owners \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Nuevo Owner",
    "email": "nuevo@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. Verificar creación
curl -X GET http://localhost:8000/api/v1/superadmin/owners/5 \
  -H "Authorization: Bearer $TOKEN"
```

### Monitorear Sistema

```bash
# Dashboard general
curl -X GET http://localhost:8000/api/v1/superadmin/dashboard \
  -H "Authorization: Bearer $TOKEN"

# Analytics del mes
curl -X GET "http://localhost:8000/api/v1/superadmin/analytics?start_date=2025-10-01&end_date=2025-10-31" \
  -H "Authorization: Bearer $TOKEN"
```

---

**Última actualización**: 5 de Octubre, 2025  
**Mantenido por**: Equipo NetFacture  
**Contacto**: dev@netfacture.ec
