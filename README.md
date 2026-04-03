# Laravel + React

This repository contains a Laravel backend and a React frontend, plus a Docker development environment.

- `backend/`: Laravel 13 application (API / server-side).
- `frontend/`: React app built with Vite.
- `docker-compose.yml`: Development services (PHP/FPM, Nginx, MySQL, Redis, Node).

You can run the project in two ways:

- With Docker (recommended)
- Locally without Docker

## Requirements

Recommended:

- Docker
- Docker Compose

If you want to run it without Docker:

- PHP 8.3+
- Composer
- Node.js 22+
- SQLite
- MySQL 8+ (optional)

## Quick start (Docker)

From the repository root:

```bash
cp backend/.env.example backend/.env
docker compose up -d --build
docker compose run --rm composer install
docker compose run --rm artisan key:generate
docker compose run --rm artisan jwt:secret --force
docker compose run --rm artisan migrate
docker compose run --rm artisan db:seed
```

This will create the app key, generate the JWT secret, run migrations and seed the demo user.

Available services:

- Backend (through Nginx): http://localhost:8080
- Frontend (Vite): http://localhost:5173
- MySQL: `localhost:3306`
- Redis: `localhost:6379`

Docker also starts a MySQL instance. If you want to use it, update `backend/.env` with:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

API documentation:

- Scramble UI: http://localhost:8080/docs/api
- OpenAPI JSON: http://localhost:8080/docs/api.json

To stop and remove containers:

```bash
docker compose down
```

## API routes

Base URL:

```text
Docker: http://localhost:8080/api/v1
Local:  http://127.0.0.1:8000/api/v1
```

Available routes:

- `POST /api/v1/login`
- `POST /api/v1/quotation` (requires `Authorization: Bearer <token>`)

## Run locally without Docker

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8000
```

By default, `backend/.env.example` uses SQLite, so you can run the project locally without much setup.
If you prefer, you can also switch `backend/.env` to MySQL and use your own local database.

### Frontend

```bash
cd frontend
npm install
VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1 npm run dev
```

Then open `http://localhost:5173`.

## Demo credentials

The seeded user for testing the login flow is:

- Email: `test@example.com`
- Password: `password`

## API headers

When testing the API, keep these headers in mind:

- `Content-Type: application/json`
- `Authorization: Bearer <JWT>` for protected routes such as `POST /api/v1/quotation`

The login route `POST /api/v1/login` only needs `Content-Type: application/json`.

## Debugging with Telescope

If you want to inspect requests while testing, Telescope is available here:

- Docker: [http://localhost:8080/telescope/requests](http://localhost:8080/telescope/requests)
- Local: [http://127.0.0.1:8000/telescope/requests](http://127.0.0.1:8000/telescope/requests)

You can use that page to inspect incoming requests, exceptions, queries and other useful debugging details while testing the API.