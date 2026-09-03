# TAP Admin Platform

Laravel 11 + Angular 19 + MongoDB (examen TAP Terminal).

## Carpetas

- `api/` backend
- `web/` frontend
- `postman/TAP-Admin-API.postman_collection.json` colección Postman

## Local

PHP 8.2 (`mongodb`, `zip`, `gd`), Composer, Node 20+, MongoDB.

```powershell
$php = "C:\xampp\php\php.exe"

cd api
copy .env.example .env
# APP_KEY, MONGODB_URI, MONGODB_DATABASE
& $php artisan key:generate
& $php artisan db:seed
& $php artisan serve

cd ..\web
npm install
npm start
```

API: http://127.0.0.1:8000  
Web: http://localhost:4200

| Email | Password | Secciones |
|-------|----------|-----------|
| admin@tap.local | Admin123! | products, users, profiles |
| operador@tap.local | Operador123! | products |

Postman: Importar `postman/TAP-Admin-API.postman_collection.json`. Login admin guarda `token`. Operator + GET `/users` = 403.

## Deploy

- Web: Vercel (`web/`, `web/vercel.json`)
- API: Render (`render.yaml`, rootDir `api/`)
- DB: MongoDB Atlas

En Vercel, `environment.prod.ts` apunta a la URL de Render. En Render: `MONGODB_URI`, `APP_KEY`, `FRONTEND_URL`, `CORS_ALLOWED_ORIGINS`.

## Tests

```powershell
cd api
& C:\xampp\php\php.exe artisan test
```
