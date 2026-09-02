# TAP Admin Platform

Examen TAP Terminal — Laravel 11 + Angular 19 + MongoDB.

## Estructura

- `api/` — Backend Laravel 11 (Sanctum, MongoDB, RBAC por sección, bitácora)
- `web/` — Frontend Angular 19

## Requisitos locales

- PHP 8.2+ con extensiones: `mongodb`, `zip`, `openssl`, `mbstring`
- Composer
- Node.js 20+
- MongoDB (local o Atlas)

### Comandos PHP en este PC (XAMPP)

```powershell
$php = "C:\xampp\php\php.exe"
$composer = "C:\xampp\php\composer.phar"
```

## API — arranque

```powershell
cd api
copy .env.example .env   # si aplica
# Editar MONGODB_URI y correo SMTP
& C:\xampp\php\php.exe artisan db:seed
& C:\xampp\php\php.exe artisan serve
```

API: http://localhost:8000

### Usuarios seed

| Email | Password | Acceso |
|-------|----------|--------|
| admin@tap.local | Admin123! | products, users, profiles |
| operador@tap.local | Operador123! | solo products |

## Web — arranque

```powershell
cd web
npm start
```

App: http://localhost:4200

## Deploy (plan)

- **Vercel** → Angular (`web/`)
- **Render** → Laravel (`api/`)
- **MongoDB Atlas M0** → base de datos

## Documentación

Ver `docs/` (SECURITY.md, DECISIONS.md — en progreso).
