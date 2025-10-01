# 🚀 Guía de Inicio Rápido - Módulo de Usuarios NetFactureEC

## ✅ Instalación Completada

El módulo de usuarios ha sido instalado y configurado exitosamente. A continuación se detallan los pasos para comenzar a usarlo.

## 📦 Componentes Instalados

### Paquetes
- ✅ `tymon/jwt-auth` (v2.2.1) - Autenticación JWT
- ✅ `spatie/laravel-permission` (v6.21.0) - Roles y permisos

### Archivos Creados
- ✅ Migraciones (usuarios, roles, permisos)
- ✅ Controladores (Auth, User, Role, Permission)
- ✅ Middleware (JWT, Role, Permission)
- ✅ Form Requests (Validaciones)
- ✅ Resources (Transformadores de datos)
- ✅ Seeders (Datos iniciales)
- ✅ Rutas API (v1)
- ✅ Documentación (README_USER_MODULE.md, SECURITY.md)
- ✅ Colección Postman

## 🎯 Usuarios de Prueba

El sistema viene con 3 usuarios pre-configurados:

| Email | Contraseña | Rol | Permisos |
|-------|------------|-----|----------|
| superadmin@netfactureec.com | Password123! | superadmin | Todos |
| admin@netfactureec.com | Password123! | admin | Gestión limitada |
| user@netfactureec.com | Password123! | user | Básicos |

## 🚦 Inicio Rápido

### 1. Verificar Base de Datos
```bash
# Asegúrate de que la base de datos esté creada
# y configurada en tu archivo .env

# Ejecutar migraciones (si no se han ejecutado)
php artisan migrate

# Ejecutar seeders (si no se han ejecutado)
php artisan db:seed
```

### 2. Probar Autenticación

#### Opción A: Con cURL
```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "superadmin@netfactureec.com",
    "password": "Password123!"
  }'

# Usar el token recibido
curl -X GET http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer {TU_TOKEN_AQUI}"
```

#### Opción B: Con Postman
1. Importar el archivo `NetFactureEC_User_Module.postman_collection.json`
2. Ejecutar el request "Login"
3. El token se guardará automáticamente
4. Probar otros endpoints

### 3. Iniciar Servidor
```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000/api/v1`

## 📊 Endpoints Principales

### Autenticación (Públicos)
```
POST   /api/v1/register    - Registrar nuevo usuario
POST   /api/v1/login       - Iniciar sesión
```

### Protegidos (Requieren JWT)
```
GET    /api/v1/me          - Obtener usuario actual
POST   /api/v1/logout      - Cerrar sesión
POST   /api/v1/refresh     - Refrescar token
```

### Gestión (Admin/Superadmin)
```
GET    /api/v1/users       - Listar usuarios
POST   /api/v1/users       - Crear usuario
GET    /api/v1/users/{id}  - Ver usuario
PUT    /api/v1/users/{id}  - Actualizar usuario
DELETE /api/v1/users/{id}  - Eliminar usuario

# Similar para /roles y /permissions
```

## 🧪 Probar el Sistema

### Test 1: Registro de Usuario
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!"
  }'
```

### Test 2: Login
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "superadmin@netfactureec.com",
    "password": "Password123!"
  }'
```

### Test 3: Obtener Usuario Actual
```bash
# Reemplaza {TOKEN} con el token recibido en el login
curl -X GET http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer {TOKEN}"
```

### Test 4: Listar Usuarios (requiere rol admin)
```bash
curl -X GET http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer {TOKEN}"
```

## 🔧 Configuración Adicional

### Cambiar Tiempo de Expiración del Token
En `.env`:
```env
JWT_TTL=60  # minutos (default: 1 hora)
```

### Habilitar/Deshabilitar Blacklist de Tokens
En `.env`:
```env
JWT_BLACKLIST_ENABLED=true
```

### Configurar CORS
En `config/cors.php`, agrega tus dominios permitidos.

## 📚 Documentación

- **README completo**: `README_USER_MODULE.md`
- **Guía de seguridad**: `SECURITY.md`
- **Colección Postman**: `NetFactureEC_User_Module.postman_collection.json`

## 🔐 Seguridad

### Contraseñas
- Mínimo 8 caracteres
- Mayúsculas y minúsculas
- Al menos un número
- Al menos un símbolo

### JWT
- Tokens expiración: 1 hora (configurable)
- Refresh tokens: 2 semanas
- Algoritmo: HS256

### Rate Limiting
Se recomienda agregar rate limiting en producción:
```php
Route::middleware('throttle:60,1')->group(function () {
    // Tus rutas
});
```

## 🐛 Troubleshooting

### Error: "Token not provided"
- Verifica que incluyas el header: `Authorization: Bearer {token}`

### Error: "Unauthenticated"
- El token puede estar expirado, usa `/refresh` o vuelve a hacer login

### Error: "No tienes permisos"
- Verifica que el usuario tenga el rol o permiso necesario
- Login con superadmin para acceso completo

### Error de CORS
- Configura `config/cors.php` con tu dominio frontend

## 📝 Próximos Pasos

1. ✅ Personalizar roles y permisos según tu negocio
2. ✅ Agregar validación de email
3. ✅ Implementar recuperación de contraseña
4. ✅ Agregar autenticación de dos factores (2FA)
5. ✅ Configurar logs de auditoría
6. ✅ Implementar rate limiting agresivo

## 🆘 Soporte

Para más información, consulta:
- `README_USER_MODULE.md` - Documentación completa
- `SECURITY.md` - Guía de seguridad
- Logs en `storage/logs/laravel.log`

## 🎉 ¡Listo!

Tu módulo de usuarios está funcionando. Comienza a construir tu API REST sobre esta base sólida y segura.

---

**Versión**: 1.0.0  
**Fecha**: Octubre 2025  
**Estado**: ✅ Producción Ready
