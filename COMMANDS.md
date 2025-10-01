# 🔧 Comandos Útiles - NetFactureEC API

## 📋 Comandos Esenciales

### Iniciar Proyecto
```bash
# Iniciar servidor de desarrollo
php artisan serve

# Iniciar en puerto específico
php artisan serve --port=8080

# Iniciar accesible desde red
php artisan serve --host=0.0.0.0 --port=8000
```

### Migraciones y Base de Datos
```bash
# Ejecutar migraciones pendientes
php artisan migrate

# Ejecutar migraciones y seeders
php artisan migrate --seed

# Rehacer toda la base de datos
php artisan migrate:fresh --seed

# Rollback última migración
php artisan migrate:rollback

# Ver estado de migraciones
php artisan migrate:status

# Ejecutar solo seeders
php artisan db:seed

# Ejecutar seeder específico
php artisan db:seed --class=RolePermissionSeeder
```

### Caché
```bash
# Limpiar toda la caché
php artisan cache:clear

# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché de rutas
php artisan route:clear

# Limpiar caché de vistas
php artisan view:clear

# Limpiar caché de permisos (Spatie)
php artisan permission:cache-reset

# Crear caché de configuración (producción)
php artisan config:cache

# Crear caché de rutas (producción)
php artisan route:cache

# Crear caché de vistas (producción)
php artisan view:cache
```

### JWT
```bash
# Generar nueva clave JWT
php artisan jwt:secret

# Forzar nueva clave JWT
php artisan jwt:secret --force

# Mostrar clave JWT actual
php artisan jwt:secret --show
```

### Información del Sistema
```bash
# Ver información general
php artisan about

# Ver rutas disponibles
php artisan route:list

# Ver solo rutas API
php artisan route:list --path=api

# Ver rutas de un método específico
php artisan route:list --method=GET

# Ver rutas con nombre
php artisan route:list --name=users

# Información de Laravel
php artisan --version
```

### Testing y Debugging
```bash
# Ejecutar tests
php artisan test

# Ejecutar tests con cobertura
php artisan test --coverage

# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar logs
> storage/logs/laravel.log

# Modo mantenimiento
php artisan down
php artisan up

# Modo mantenimiento con mensaje
php artisan down --message="Mantenimiento programado" --retry=60
```

### Generadores (para desarrollo futuro)
```bash
# Crear controlador
php artisan make:controller NombreController

# Crear controlador API
php artisan make:controller Api/NombreController --api

# Crear modelo
php artisan make:model Nombre

# Crear modelo con migración
php artisan make:model Nombre -m

# Crear modelo completo
php artisan make:model Nombre -mfsc

# Crear migración
php artisan make:migration create_tabla_name

# Crear seeder
php artisan make:seeder NombreSeeder

# Crear middleware
php artisan make:middleware NombreMiddleware

# Crear request
php artisan make:request NombreRequest

# Crear resource
php artisan make:resource NombreResource

# Crear factory
php artisan make:factory NombreFactory

# Crear observer
php artisan make:observer NombreObserver
```

### Composer
```bash
# Instalar dependencias
composer install

# Actualizar dependencias
composer update

# Instalar nueva dependencia
composer require vendor/package

# Desinstalar dependencia
composer remove vendor/package

# Verificar vulnerabilidades
composer audit

# Ver paquetes desactualizados
composer outdated

# Actualizar autoload
composer dump-autoload

# Optimizar autoload (producción)
composer dump-autoload --optimize
```

### Permisos y Roles (Spatie)
```bash
# Limpiar caché de permisos
php artisan permission:cache-reset

# Crear comando personalizado para gestionar permisos
php artisan make:command AssignPermission
```

### Optimización para Producción
```bash
# Cachear todo
php artisan optimize

# Limpiar todo
php artisan optimize:clear

# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar composer
composer install --optimize-autoloader --no-dev
```

### Git (Control de Versiones)
```bash
# Estado del repositorio
git status

# Agregar cambios
git add .

# Commit
git commit -m "Mensaje del commit"

# Ver historial
git log --oneline

# Crear nueva rama
git checkout -b feature/nueva-funcionalidad

# Cambiar de rama
git checkout main

# Ver ramas
git branch

# Mergear rama
git merge feature/nueva-funcionalidad
```

## 🔍 Comandos de Diagnóstico

### Verificar Instalación
```bash
# PHP version
php --version

# Composer version
composer --version

# Laravel version
php artisan --version

# Verificar extensiones PHP
php -m

# Verificar configuración PHP
php -i
```

### Verificar Base de Datos
```bash
# Conectar a MySQL
mysql -u root -p

# Mostrar bases de datos
SHOW DATABASES;

# Usar base de datos
USE netfacture_ec;

# Mostrar tablas
SHOW TABLES;

# Ver usuarios
SELECT * FROM users;

# Ver roles
SELECT * FROM roles;

# Ver permisos
SELECT * FROM permissions;
```

### Debug de Errores
```bash
# Ver logs de Laravel
cat storage/logs/laravel.log

# Ver últimas 50 líneas del log
tail -n 50 storage/logs/laravel.log

# Seguir logs en tiempo real
tail -f storage/logs/laravel.log

# Buscar errores específicos
grep "ERROR" storage/logs/laravel.log

# Limpiar logs
> storage/logs/laravel.log
```

## 🚀 Scripts Útiles Personalizados

### Script de Reinicio Completo
```bash
#!/bin/bash
# reset.sh - Reiniciar proyecto completamente

echo "🔄 Reiniciando proyecto..."

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reinstalar base de datos
php artisan migrate:fresh --seed

# Regenerar permisos
php artisan permission:cache-reset

# Optimizar
composer dump-autoload

echo "✅ Proyecto reiniciado!"
```

### Script de Backup
```bash
#!/bin/bash
# backup.sh - Crear backup de la base de datos

DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p netfacture_ec > backup_$DATE.sql
echo "✅ Backup creado: backup_$DATE.sql"
```

### Script de Deploy
```bash
#!/bin/bash
# deploy.sh - Deploy a producción

echo "🚀 Iniciando deploy..."

# Pull cambios
git pull origin main

# Instalar dependencias
composer install --optimize-autoloader --no-dev

# Ejecutar migraciones
php artisan migrate --force

# Limpiar y cachear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permisos
chmod -R 775 storage bootstrap/cache

echo "✅ Deploy completado!"
```

## 📝 Comandos de API Testing

### cURL Ejemplos
```bash
# Login
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@netfactureec.com","password":"Password123!"}'

# Guardar token en variable
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@netfactureec.com","password":"Password123!"}' \
  | jq -r '.data.token')

# Usar token
curl -X GET http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer $TOKEN"

# Listar usuarios
curl -X GET http://localhost:8000/api/v1/users \
  -H "Authorization: Bearer $TOKEN"
```

## 🔐 Comandos de Seguridad

### Generar Claves
```bash
# Generar APP_KEY
php artisan key:generate

# Generar JWT_SECRET
php artisan jwt:secret

# Regenerar ambas
php artisan key:generate && php artisan jwt:secret --force
```

### Verificar Seguridad
```bash
# Verificar vulnerabilidades en dependencias
composer audit

# Ver dependencias desactualizadas
composer outdated

# Actualizar dependencias de seguridad
composer update --with-dependencies
```

## 📊 Comandos de Monitoreo

### Performance
```bash
# Ver queries lentas
php artisan tinker
>>> DB::enableQueryLog();
>>> // Ejecutar operación
>>> DB::getQueryLog();

# Ver caché hit ratio
php artisan cache:table
```

### Sistema
```bash
# Ver procesos PHP
ps aux | grep php

# Ver uso de memoria
free -h

# Ver espacio en disco
df -h

# Ver logs del sistema
journalctl -u php-fpm -f
```

## 🛠️ Mantenimiento

### Limpieza
```bash
# Limpiar sesiones expiradas
php artisan session:gc

# Limpiar caché expirada
php artisan cache:prune-stale-tags

# Limpiar trabajos fallidos
php artisan queue:flush
```

### Actualización
```bash
# Ver versión actual
php artisan --version

# Actualizar Laravel (con cuidado)
composer update laravel/framework

# Actualizar todas las dependencias
composer update
```

---

## 💡 Tips

### Alias Útiles (añadir a .bashrc o .zshrc)
```bash
alias pa='php artisan'
alias pas='php artisan serve'
alias pam='php artisan migrate'
alias pac='php artisan cache:clear'
alias par='php artisan route:list'
alias pat='php artisan test'
alias cu='composer update'
alias ci='composer install'
```

### Variables de Entorno Importantes
```env
APP_ENV=local|production
APP_DEBUG=true|false
JWT_TTL=60
JWT_REFRESH_TTL=20160
DB_CONNECTION=mysql
CACHE_DRIVER=redis|database
QUEUE_CONNECTION=sync|redis|database
```

---

**💻 Comandos actualizados para Laravel 12 y NetFactureEC v1.0.0**  
**Última actualización**: Octubre 2025
