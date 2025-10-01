# 🔐 Guía de Seguridad - Módulo de Usuarios NetFactureEC

## 📋 Índice
1. [Configuración de Seguridad](#configuración-de-seguridad)
2. [Protección contra Ataques](#protección-contra-ataques)
3. [Best Practices](#best-practices)
4. [Checklist de Seguridad](#checklist-de-seguridad)

## Configuración de Seguridad

### 1. Variables de Entorno (.env)

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... # Generado con: php artisan key:generate

# JWT Configuration
JWT_SECRET=... # Generado automáticamente
JWT_TTL=60 # 1 hora
JWT_REFRESH_TTL=20160 # 2 semanas
JWT_ALGO=HS256
JWT_BLACKLIST_ENABLED=true
JWT_BLACKLIST_GRACE_PERIOD=30

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=netfacture_ec
DB_USERNAME=netfacture_user
DB_PASSWORD=strong_password_here

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

# Cache
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# CORS
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### 2. Configuración de CORS (config/cors.php)

```php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'https://yourdomain.com'
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### 3. Configuración de Rate Limiting

En `bootstrap/app.php` o en tus rutas:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

// Configuración global
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// Rate limiting para login (prevenir fuerza bruta)
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

Aplicar en rutas:
```php
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login');
```

## Protección contra Ataques

### 1. SQL Injection
✅ **Protegido automáticamente por:**
- Eloquent ORM
- Query Builder
- Prepared Statements

**Ejemplo seguro:**
```php
// ✅ CORRECTO
User::where('email', $request->email)->first();

// ❌ INCORRECTO (nunca hacer esto)
DB::select("SELECT * FROM users WHERE email = '{$request->email}'");
```

### 2. XSS (Cross-Site Scripting)
✅ **Protegido por:**
- Blade templates (escape automático)
- Validación de entrada
- Sanitización en Resources

**Ejemplo:**
```php
// En validación
'name' => ['required', 'string', 'max:255'],

// En Resources
return [
    'name' => $this->name, // Ya sanitizado
];
```

### 3. CSRF (Cross-Site Request Forgery)
✅ **Para APIs REST:**
- JWT tokens reemplazan CSRF tokens
- Validación de origen (CORS)
- Tokens de corta duración

### 4. Brute Force Attacks
✅ **Protegido por:**
- Rate Limiting en endpoints de autenticación
- Bloqueo temporal de cuentas
- Logging de intentos fallidos

**Implementar bloqueo de cuenta:**
```php
// En LoginRequest o Controller
if ($failedAttempts > 5) {
    $user->update(['is_active' => false]);
    // Enviar email de alerta
}
```

### 5. JWT Token Attacks

#### Token Replay Attack
```php
// Usar blacklist de tokens
JWT_BLACKLIST_ENABLED=true

// Al hacer logout
JWTAuth::invalidate(JWTAuth::getToken());
```

#### Token Expiration
```php
// Tokens de corta duración
JWT_TTL=60 # 1 hora

// Implementar refresh automático en frontend
```

### 6. Mass Assignment
✅ **Protegido por:**
```php
// En modelo User.php
protected $fillable = [
    'name',
    'email',
    'password',
    // Solo campos permitidos
];

protected $guarded = ['id', 'is_admin'];
```

### 7. Sensitive Data Exposure
✅ **Protegido por:**
```php
// En modelo User.php
protected $hidden = [
    'password',
    'remember_token',
];

// En Resources
public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        // NO incluir password, tokens, etc.
    ];
}
```

## Best Practices

### 1. Validación de Contraseñas Seguras

```php
use Illuminate\Validation\Rules\Password;

Password::min(8)
    ->mixedCase()      // Al menos una mayúscula y minúscula
    ->letters()        // Al menos una letra
    ->numbers()        // Al menos un número
    ->symbols()        // Al menos un símbolo
    ->uncompromised(); // No estar en data breaches
```

### 2. Hash de Contraseñas

```php
// ✅ CORRECTO - Usar Hash facade
use Illuminate\Support\Facades\Hash;

Hash::make($password); // Crea hash bcrypt

// ✅ Verificar contraseña
Hash::check($plainPassword, $hashedPassword);

// ❌ INCORRECTO
md5($password); // NUNCA usar MD5 o SHA1
```

### 3. Logging de Seguridad

```php
// En AuthController
Log::channel('security')->info('Login exitoso', [
    'user_id' => $user->id,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);

Log::channel('security')->warning('Intento de login fallido', [
    'email' => $request->email,
    'ip' => $request->ip(),
]);
```

### 4. HTTPS Obligatorio en Producción

En `app/Providers/AppServiceProvider.php`:
```php
public function boot()
{
    if ($this->app->environment('production')) {
        URL::forceScheme('https');
    }
}
```

### 5. Headers de Seguridad

En middleware o servidor web:
```php
// Agregar headers de seguridad
return $next($request)
    ->header('X-Content-Type-Options', 'nosniff')
    ->header('X-Frame-Options', 'DENY')
    ->header('X-XSS-Protection', '1; mode=block')
    ->header('Strict-Transport-Security', 'max-age=31536000');
```

### 6. Sanitización de Datos

```php
// Siempre validar y sanitizar entrada
$validated = $request->validate([
    'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
    'email' => ['required', 'email', 'max:255'],
]);

// Usar trim, strip_tags si es necesario
$name = trim(strip_tags($request->name));
```

### 7. Actualización de Dependencias

```bash
# Verificar vulnerabilidades
composer audit

# Actualizar dependencias
composer update

# Verificar outdated packages
composer outdated
```

## Checklist de Seguridad

### Pre-Producción

- [ ] Variables de entorno configuradas en .env
- [ ] APP_DEBUG=false en producción
- [ ] JWT_SECRET generado y seguro
- [ ] Base de datos con usuario no-root
- [ ] HTTPS habilitado
- [ ] CORS configurado correctamente
- [ ] Rate limiting implementado
- [ ] Logs de seguridad activados
- [ ] Backups automáticos configurados
- [ ] Tokens con expiración corta
- [ ] Validación de entrada en todos los endpoints
- [ ] Contraseñas con política de complejidad
- [ ] Headers de seguridad configurados
- [ ] Dependencias actualizadas (sin vulnerabilidades)

### Post-Producción

- [ ] Monitoreo de logs activo
- [ ] Alertas de intentos de login fallidos
- [ ] Revisión periódica de permisos
- [ ] Auditoría de usuarios activos
- [ ] Actualización de dependencias mensual
- [ ] Revisión de accesos no autorizados
- [ ] Backup y restore testing
- [ ] Penetration testing periódico

## Comandos Útiles de Seguridad

```bash
# Limpiar caché de configuración
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Regenerar JWT secret
php artisan jwt:secret --force

# Limpiar caché de permisos
php artisan permission:cache-reset

# Ver rutas protegidas
php artisan route:list --except-vendor

# Verificar vulnerabilidades en dependencias
composer audit

# Generar nueva APP_KEY
php artisan key:generate
```

## Respuesta a Incidentes

### Si detectas acceso no autorizado:

1. **Inmediatamente:**
   - Invalidar todos los tokens JWT
   - Deshabilitar cuenta comprometida
   - Cambiar JWT_SECRET
   - Revisar logs de acceso

2. **Análisis:**
   - Identificar punto de entrada
   - Revisar cambios en base de datos
   - Verificar otros usuarios afectados

3. **Remediación:**
   - Parchear vulnerabilidad
   - Resetear contraseñas afectadas
   - Notificar a usuarios
   - Documentar incidente

4. **Prevención:**
   - Implementar controles adicionales
   - Mejorar monitoreo
   - Capacitar al equipo

## Recursos Adicionales

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [JWT Security Best Practices](https://tools.ietf.org/html/rfc8725)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)

---

**Nota importante:** La seguridad es un proceso continuo. Mantén tu aplicación actualizada y revisa periódicamente las mejores prácticas.
