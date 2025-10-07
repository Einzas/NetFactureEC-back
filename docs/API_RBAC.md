# 🎭 Sistema RBAC - NetFacture Professional

**Versión**: 2.0.0  
**Última actualización**: Octubre 2025

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Arquitectura RBAC](#arquitectura-rbac)
3. [Permisos del Sistema](#permisos-del-sistema)
4. [Roles del Sistema](#roles-del-sistema)
5. [Asignación de Permisos](#asignación-de-permisos)
6. [Verificación de Permisos](#verificación-de-permisos)
7. [Permisos Directos](#permisos-directos)
8. [Ejemplos de Uso](#ejemplos-de-uso)

---

## Introducción

El sistema RBAC (Role-Based Access Control) de NetFacture Professional permite un control granular de permisos a nivel de empleado, combinando permisos heredados de roles con permisos directos (granted/revoked).

### Características Principales

✅ **42 Permisos Granulares** en 9 módulos  
✅ **6 Roles del Sistema** predefinidos  
✅ **Roles Personalizados** por empresa  
✅ **Permisos Directos** (grant/revoke)  
✅ **Multi-Rol** por empleado  
✅ **Scope por Empresa** automático

---

## Arquitectura RBAC

### Modelo de Capas

```
┌─────────────────────────────────────────────────┐
│                  EMPLOYEE                       │
│  (Empleado con acceso al sistema)               │
└────────────┬────────────────────────────────────┘
             │
             ├─────────────┬──────────────┐
             │             │              │
             ▼             ▼              ▼
      ┌──────────┐   ┌──────────┐  ┌──────────────┐
      │  ROLE 1  │   │  ROLE 2  │  │ PERMISSIONS  │
      │  (Admin) │   │(Contador)│  │  (Directos)  │
      └────┬─────┘   └────┬─────┘  └──────┬───────┘
           │              │               │
           │              │               ├─ granted: true
           │              │               └─ granted: false (revoked)
           │              │
           ▼              ▼
    ┌──────────────┐  ┌──────────────┐
    │ PERMISSIONS  │  │ PERMISSIONS  │
    │  (37 perms)  │  │  (18 perms)  │
    └──────────────┘  └──────────────┘
           │              │
           └──────┬───────┘
                  │
                  ▼
       ┌──────────────────────────┐
       │  RESOLUCIÓN FINAL:       │
       │  1. Permisos de rol 1    │
       │  2. + Permisos de rol 2  │
       │  3. + Permisos granted   │
       │  4. - Permisos revoked   │
       └──────────────────────────┘
```

### Tablas de Base de Datos

```sql
-- Permisos
permissions (id, name, display_name, description, module)

-- Roles
roles (id, name, display_name, description, is_system, company_id)

-- Pivot: Rol → Permisos
permission_role (permission_id, role_id)

-- Pivot: Empleado → Roles
employee_role (employee_id, role_id)

-- Pivot: Empleado → Permisos Directos
employee_permission (employee_id, permission_id, granted)
```

---

## Permisos del Sistema

### Módulos y Permisos (42 total)

#### 1. Companies (5 permisos)
```
companies.view        - Ver empresas
companies.create      - Crear empresas
companies.edit        - Editar empresas
companies.delete      - Eliminar empresas
companies.settings    - Configurar empresas
```

#### 2. Employees (6 permisos)
```
employees.view              - Ver empleados
employees.create            - Crear empleados
employees.edit              - Editar empleados
employees.delete            - Eliminar empleados
employees.manage-roles      - Asignar roles
employees.manage-permissions - Asignar permisos directos
```

#### 3. Roles (4 permisos)
```
roles.view      - Ver roles
roles.create    - Crear roles personalizados
roles.edit      - Editar roles personalizados
roles.delete    - Eliminar roles personalizados
```

#### 4. Files (5 permisos)
```
files.view      - Ver archivos
files.upload    - Subir archivos
files.download  - Descargar archivos
files.delete    - Eliminar archivos
files.manage    - Gestionar archivos de otros
```

#### 5. Invoices (6 permisos)
```
invoices.view       - Ver facturas
invoices.create     - Crear facturas
invoices.edit       - Editar facturas
invoices.delete     - Eliminar facturas
invoices.authorize  - Autorizar en SRI
invoices.cancel     - Anular facturas
```

#### 6. Credit Notes (3 permisos)
```
credit-notes.view       - Ver notas de crédito
credit-notes.create     - Crear notas de crédito
credit-notes.authorize  - Autorizar en SRI
```

#### 7. Withholdings (3 permisos)
```
withholdings.view       - Ver retenciones
withholdings.create     - Crear retenciones
withholdings.authorize  - Autorizar en SRI
```

#### 8. Reports (3 permisos)
```
reports.view       - Ver reportes
reports.export     - Exportar reportes
reports.analytics  - Dashboard analítico
```

#### 9. Settings (3 permisos)
```
settings.view  - Ver configuración
settings.edit  - Editar configuración
settings.sri   - Configurar SRI
```

#### 10. Users (4 permisos) - SOLO SUPERADMIN
```
users.view    - Ver usuarios del sistema
users.create  - Crear usuarios del sistema
users.edit    - Editar usuarios del sistema
users.delete  - Eliminar usuarios del sistema
```

---

## Roles del Sistema

### 1. Administrador Total

**Name**: `admin`  
**Display**: Administrador Total  
**Permisos**: 37 (todos excepto `users.*`)

```php
$admin->hasPermission('companies.create');   // true
$admin->hasPermission('invoices.authorize'); // true
$admin->hasPermission('employees.delete');   // true
$admin->hasPermission('users.create');       // false (solo superadmin)
```

**Uso**: Gerentes, administradores de empresa

### 2. Contador

**Name**: `contador`  
**Display**: Contador  
**Permisos**: 18

**Módulos**:
- Invoices: view, create, edit, authorize
- Credit Notes: view, create, authorize
- Withholdings: view, create, authorize
- Reports: view, export, analytics
- Files: view, upload, download
- Settings: view

**Uso**: Personal contable

### 3. Facturador

**Name**: `facturador`  
**Display**: Facturador  
**Permisos**: 9

**Módulos**:
- Invoices: view, create, edit, authorize
- Files: view, upload, download
- Reports: view, export

**Uso**: Personal de facturación

### 4. Vendedor

**Name**: `vendedor`  
**Display**: Vendedor  
**Permisos**: 3

**Módulos**:
- Invoices: view, create
- Reports: view

**Uso**: Equipo de ventas

### 5. Auditor

**Name**: `auditor`  
**Display**: Auditor (Solo Lectura)  
**Permisos**: 13 (solo `*.view`)

**Módulos**:
- Companies: view
- Employees: view
- Invoices: view
- Credit Notes: view
- Withholdings: view
- Files: view, download
- Reports: view, export, analytics

**Uso**: Auditores, supervisores

### 6. Asistente Administrativo

**Name**: `asistente`  
**Display**: Asistente Administrativo  
**Permisos**: 5

**Módulos**:
- Files: view, upload, download
- Reports: view, export

**Uso**: Personal administrativo

---

## Asignación de Permisos

### Asignar Rol a Empleado

**Endpoint**: `POST /api/v1/employee/employees/{id}/assign-role`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request**:
```json
{
  "role_id": 1
}
```

**Método PHP**:
```php
$employee = Employee::find(1);
$employee->assignRole('admin');
// o
$employee->assignRole(1); // por ID
```

**Response** (200):
```json
{
  "success": true,
  "message": "Rol asignado exitosamente",
  "data": {
    "employee_id": 1,
    "role": {
      "id": 1,
      "name": "admin",
      "display_name": "Administrador Total"
    }
  }
}
```

### Remover Rol de Empleado

**Endpoint**: `DELETE /api/v1/employee/employees/{id}/remove-role`

**Request**:
```json
{
  "role_id": 1
}
```

**Método PHP**:
```php
$employee->removeRole('admin');
```

### Asignar Múltiples Roles

**Método PHP**:
```php
$employee->assignRole(['admin', 'contador']);
```

---

## Verificación de Permisos

### Verificar Permiso Simple

**Endpoint**: `POST /api/v1/employee/check-permission`

**Request**:
```json
{
  "permission": "invoices.create"
}
```

**Response** (200):
```json
{
  "success": true,
  "data": {
    "permission": "invoices.create",
    "has_permission": true
  }
}
```

**Método PHP**:
```php
$employee = auth('employee')->user();

// Verificar un permiso
if ($employee->can('invoices.create')) {
    // Permitir acción
}

// Verificar rol
if ($employee->hasRole('admin')) {
    // Es administrador
}

// Verificar cualquier permiso
if ($employee->hasAnyPermission(['invoices.create', 'invoices.edit'])) {
    // Tiene al menos uno
}

// Verificar todos los permisos
if ($employee->hasAllPermissions(['invoices.view', 'invoices.create'])) {
    // Tiene todos
}
```

### Middleware de Permisos

**En rutas**:
```php
Route::middleware(['auth:employee', 'permission:invoices.create'])
    ->post('/invoices', [InvoiceController::class, 'store']);

Route::middleware(['auth:employee', 'role:admin'])
    ->get('/employees', [EmployeeController::class, 'index']);
```

**Respuesta sin permiso** (403):
```json
{
  "success": false,
  "message": "No autorizado",
  "errors": {
    "permission": ["No tienes el permiso requerido: invoices.create"]
  }
}
```

**Respuesta sin rol** (403):
```json
{
  "success": false,
  "message": "No autorizado",
  "errors": {
    "role": ["No tienes el rol requerido: admin"]
  }
}
```

---

## Permisos Directos

### Dar Permiso Directo (Grant)

**Endpoint**: `POST /api/v1/employee/employees/{id}/give-permission`

**Request**:
```json
{
  "permission": "files.delete"
}
```

**Método PHP**:
```php
$employee->givePermissionTo('files.delete');
```

**Response** (200):
```json
{
  "success": true,
  "message": "Permiso otorgado",
  "data": {
    "employee_id": 1,
    "permission": "files.delete",
    "granted": true
  }
}
```

**Caso de uso**:
Un vendedor normalmente no puede eliminar archivos, pero este vendedor específico sí necesita ese permiso.

### Revocar Permiso (Revoke)

**Endpoint**: `POST /api/v1/employee/employees/{id}/revoke-permission`

**Request**:
```json
{
  "permission": "files.upload"
}
```

**Método PHP**:
```php
$employee->revokePermissionTo('files.upload');
```

**Response** (200):
```json
{
  "success": true,
  "message": "Permiso revocado",
  "data": {
    "employee_id": 1,
    "permission": "files.upload",
    "granted": false
  }
}
```

**Caso de uso**:
Un contador tiene permiso de subir archivos por su rol, pero este contador específico no debe poder subir archivos.

### Ver Permisos de Empleado

**Endpoint**: `GET /api/v1/employee/employees/{id}/permissions`

**Response** (200):
```json
{
  "success": true,
  "data": {
    "employee_id": 1,
    "roles": [
      {
        "id": 1,
        "name": "admin",
        "permissions_count": 37
      }
    ],
    "permissions_from_roles": [
      "companies.view",
      "companies.create",
      "invoices.create"
      // ... 37 permisos
    ],
    "direct_permissions": {
      "granted": [
        "files.delete"
      ],
      "revoked": [
        "files.upload"
      ]
    },
    "effective_permissions": [
      "companies.view",
      "companies.create",
      "invoices.create",
      "files.delete"
      // files.upload NO está (fue revocado)
    ]
  }
}
```

---

## Ejemplos de Uso

### Ejemplo 1: Contador con Permiso Extra

```php
// Ana es contadora
$ana = Employee::where('email', 'contador.tec@tecsoluciones.com')->first();
$ana->assignRole('contador');

// Verificar permisos base
$ana->can('invoices.create');    // true (por rol)
$ana->can('employees.create');   // false (no tiene el permiso)

// Dar permiso extra para crear empleados
$ana->givePermissionTo('employees.create');

// Ahora sí puede
$ana->can('employees.create');   // true (permiso directo)

// Ver todos sus permisos
$permissions = $ana->getAllPermissions();
// Retorna Collection con 19 permisos (18 de rol + 1 directo)
```

### Ejemplo 2: Vendedor con Restricción

```php
// Carlos es vendedor
$carlos = Employee::where('email', 'ventas1.tec@tecsoluciones.com')->first();
$carlos->assignRole('vendedor');

// Puede crear facturas
$carlos->can('invoices.create');  // true

// Por alguna razón, este vendedor no debe crear facturas
$carlos->revokePermissionTo('invoices.create');

// Ahora no puede
$carlos->can('invoices.create');  // false
```

### Ejemplo 3: Multi-Rol

```php
// Laura tiene 2 roles
$laura = Employee::find(10);
$laura->assignRole(['contador', 'facturador']);

// Tiene permisos de ambos roles
$laura->can('withholdings.create');  // true (de contador)
$laura->can('invoices.authorize');   // true (de ambos)

// Verificar roles
$laura->hasRole('contador');    // true
$laura->hasRole('admin');       // false
```

### Ejemplo 4: Roles Personalizados

```php
// Crear rol personalizado para la empresa
$customRole = Role::create([
    'name' => 'supervisor-ventas',
    'display_name' => 'Supervisor de Ventas',
    'description' => 'Supervisa equipo de ventas',
    'is_system' => false,
    'company_id' => 1
]);

// Asignar permisos específicos
$customRole->givePermissionTo([
    'invoices.view',
    'invoices.create',
    'invoices.edit',
    'reports.view',
    'reports.analytics',
    'employees.view'
]);

// Asignar a empleado
$supervisor = Employee::find(15);
$supervisor->assignRole('supervisor-ventas');
```

### Ejemplo 5: Verificación en Controladores

```php
class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        $employee = auth('employee')->user();
        
        // Verificación manual
        if (!$employee->can('invoices.create')) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }
        
        // Crear factura...
    }
    
    // O usar middleware en routes
    // Route::post('/invoices', [InvoiceController::class, 'store'])
    //     ->middleware('permission:invoices.create');
}
```

### Ejemplo 6: Gate en Blade (Futuro Frontend)

```php
// En el controlador
Gate::define('authorize-invoice', function ($employee) {
    return $employee->can('invoices.authorize');
});

// En la vista (futuro)
@can('authorize-invoice')
    <button>Autorizar Factura</button>
@endcan
```

---

## Matriz de Permisos por Rol

| Permiso | Admin | Contador | Facturador | Vendedor | Auditor | Asistente |
|---------|:-----:|:--------:|:----------:|:--------:|:-------:|:---------:|
| **COMPANIES** |
| companies.view | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| companies.create | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| companies.edit | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| companies.delete | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| companies.settings | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **EMPLOYEES** |
| employees.view | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| employees.create | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| employees.edit | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| employees.delete | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| employees.manage-roles | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| employees.manage-permissions | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **ROLES** |
| roles.view | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| roles.create | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| roles.edit | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| roles.delete | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **FILES** |
| files.view | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| files.upload | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| files.download | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| files.delete | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| files.manage | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **INVOICES** |
| invoices.view | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| invoices.create | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| invoices.edit | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| invoices.delete | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| invoices.authorize | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| invoices.cancel | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **CREDIT NOTES** |
| credit-notes.view | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| credit-notes.create | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| credit-notes.authorize | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **WITHHOLDINGS** |
| withholdings.view | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| withholdings.create | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| withholdings.authorize | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **REPORTS** |
| reports.view | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| reports.export | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| reports.analytics | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| **SETTINGS** |
| settings.view | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| settings.edit | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| settings.sri | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **TOTAL PERMISOS** | **37** | **18** | **9** | **3** | **13** | **5** |

---

## Best Practices

### 1. Usar Roles, No Permisos Directos

```php
// ✅ CORRECTO
$employee->assignRole('contador');

// ❌ EVITAR (solo usar cuando sea realmente necesario)
$employee->givePermissionTo('invoices.view');
$employee->givePermissionTo('invoices.create');
$employee->givePermissionTo('invoices.edit');
// ... (mejor usar un rol)
```

### 2. Nombres de Permisos Consistentes

```php
// ✅ CORRECTO
'invoices.view'
'invoices.create'
'invoices.edit'

// ❌ INCORRECTO
'view-invoices'
'createInvoice'
'Invoice_Edit'
```

### 3. Verificar en Controladores Y Rutas

```php
// ✅ CORRECTO - Doble verificación
Route::middleware(['permission:invoices.create'])
    ->post('/invoices', [InvoiceController::class, 'store']);

public function store(Request $request)
{
    // Verificación adicional con lógica de negocio
    if (!auth('employee')->user()->can('invoices.create')) {
        abort(403);
    }
    // ...
}
```

### 4. Cache de Permisos

```php
// En Employee.php
public function getAllPermissions()
{
    return Cache::remember("employee_{$this->id}_permissions", 3600, function () {
        // Lógica de obtención de permisos...
    });
}

// Limpiar cache al cambiar permisos
public function assignRole($role)
{
    Cache::forget("employee_{$this->id}_permissions");
    // Asignar rol...
}
```

---

**Última actualización**: 5 de Octubre, 2025  
**Mantenido por**: Equipo NetFacture  
**Contacto**: dev@netfacture.ec
