# 🧾 NetFactureEC - API REST Backend

## 📋 Descripción

**NetFactureEC** es una API REST profesional construida con Laravel 12 que proporciona un sistema completo de autenticación y gestión de usuarios con JWT, roles y permisos.

### 🎯 Características Principales

- ✅ **Autenticación JWT** - Tokens seguros con expiración configurable
- ✅ **Sistema de Roles y Permisos** - Autorización granular con Spatie Permission
- ✅ **Seguridad Avanzada** - Bcrypt, validación de contraseñas, rate limiting
- ✅ **API RESTful** - Endpoints bien estructurados y documentados
- ✅ **Validación Robusta** - Form Requests con validación exhaustiva
- ✅ **Respuestas Consistentes** - API Resources para transformación de datos
- ✅ **Soft Deletes** - Usuarios marcados como inactivos en lugar de eliminar
- ✅ **Auditoría Básica** - Tracking de login y actividad de usuarios

## 🚀 Inicio Rápido

### Requisitos Previos

- PHP >= 8.2
- Composer
- MySQL >= 8.0
- Laravel 12

### Instalación

```bash
# Clonar repositorio
git clone <tu-repositorio>
cd netfacture_ec

# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Configurar base de datos en .env
DB_DATABASE=netfacture_ec
DB_USERNAME=root
DB_PASSWORD=

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Iniciar servidor
php artisan serve
```

La API estará disponible en: `http://localhost:8000/api/v1`

### Usuarios de Prueba

| Email | Contraseña | Rol |
|-------|------------|-----|
| superadmin@netfactureec.com | Password123! | superadmin |
| admin@netfactureec.com | Password123! | admin |
| user@netfactureec.com | Password123! | user |

## 📚 Documentación

- **[README_USER_MODULE.md](README_USER_MODULE.md)** - Documentación completa del módulo de usuarios
- **[SECURITY.md](SECURITY.md)** - Guía de seguridad y mejores prácticas
- **[QUICKSTART.md](QUICKSTART.md)** - Guía de inicio rápido
- **[PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md)** - Estructura del proyecto
- **[INTEGRATION_EXAMPLES.md](INTEGRATION_EXAMPLES.md)** - Ejemplos de integración

## 🔌 Endpoints Principales

### Autenticación (Público)
```
POST /api/v1/register    - Registrar usuario
POST /api/v1/login       - Iniciar sesión
```

### Protegidos (JWT)
```
GET  /api/v1/me          - Usuario actual
POST /api/v1/logout      - Cerrar sesión
POST /api/v1/refresh     - Refrescar token
```

### Gestión (Admin/Superadmin)
```
GET    /api/v1/users         - Listar usuarios
POST   /api/v1/users         - Crear usuario
GET    /api/v1/users/{id}    - Ver usuario
PUT    /api/v1/users/{id}    - Actualizar usuario
DELETE /api/v1/users/{id}    - Eliminar usuario

GET    /api/v1/roles         - Gestión de roles
GET    /api/v1/permissions   - Gestión de permisos
```

## 🧪 Testing

### Con Postman
Importa la colección: `NetFactureEC_User_Module.postman_collection.json`

### Con cURL
```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@netfactureec.com","password":"Password123!"}'

# Usar token
curl -X GET http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer {TU_TOKEN}"
```

## 🔐 Seguridad

### Implementada
- JWT con expiración de 1 hora
- Contraseñas con Bcrypt
- Validación de complejidad de contraseñas
- Roles y permisos granulares
- Soft deletes
- Protección SQL Injection (Eloquent)
- Protección Mass Assignment
- CORS configurable

### Recomendado para Producción
- Rate limiting
- HTTPS obligatorio
- 2FA (Two-Factor Authentication)
- Logs de auditoría
- Recuperación de contraseña
- Verificación de email

## 📦 Tecnologías Utilizadas

- **Laravel 12** - Framework PHP
- **tymon/jwt-auth** - Autenticación JWT
- **spatie/laravel-permission** - Roles y permisos
- **MySQL** - Base de datos
- **Bcrypt** - Hash de contraseñas

## 📊 Estructura del Proyecto

```
netfacture_ec/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/    # Controladores API
│   │   ├── Middleware/         # Middleware personalizado
│   │   ├── Requests/           # Form Requests
│   │   └── Resources/          # API Resources
│   └── Models/                 # Modelos Eloquent
├── database/
│   ├── migrations/             # Migraciones
│   └── seeders/                # Seeders
├── routes/
│   └── api.php                 # Rutas API
└── config/                     # Configuración
```

## 🛠️ Comandos Útiles

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Ver rutas
php artisan route:list --path=api

# Regenerar permisos
php artisan permission:cache-reset

# Re-ejecutar migraciones
php artisan migrate:fresh --seed
```

## 🤝 Contribución

Este es un proyecto privado. Para contribuir, contacta al equipo de desarrollo.

## 📝 Licencia

Proyecto propietario - Todos los derechos reservados.

## 📞 Soporte

Para soporte técnico o consultas:
- Email: dev@netfactureec.com
- Documentación: Ver archivos .md en el proyecto

---

**Versión**: 1.0.0  
**Fecha**: Octubre 2025  
**Estado**: ✅ Listo para Producción

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
