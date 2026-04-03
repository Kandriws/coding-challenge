# Laravel + React

This repository contains a Laravel backend and a React frontend, plus a Docker development environment.

- `backend/`: Laravel 13 application (API / server-side).
- `frontend/`: React app built with Vite.
- `docker-compose.yml`: Development services (PHP/FPM, Nginx, MySQL, Redis, Node).

## Requirements

Preferred: Docker (recommended)

- Docker
- Docker Compose

Alternatively, run locally with:

- PHP 8.3+
- Composer
- Node.js 22+
- MySQL 8+
- Redis

## Quick start (Docker)

From the repository root:

```bash
docker compose up -d --build
docker compose run --rm composer install
cp backend/.env.example backend/.env
docker compose run --rm artisan key:generate
docker compose run --rm artisan migrate
```

Available services:

- Backend (through Nginx): http://localhost:8080
- Frontend (Vite): http://localhost:5173
- MySQL: `localhost:3306`
- Redis: `localhost:6379`

To stop and remove containers:

```bash
docker compose down
```

## Run locally without Docker

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run dev
```

### Frontend

```bash
cd frontend
npm install
npm run dev
```

## Useful commands

Backend:

```bash
cd backend
php artisan test
php artisan migrate
php artisan make:controller NameController
```

Frontend:

```bash
cd frontend
npm run dev
npm run build
npm run lint
```

With Docker:

```bash
docker compose run --rm artisan test
docker compose run --rm artisan migrate
docker compose run --rm node install
docker compose run --rm node run build
```

## Basic structure

```text
.
├── backend
├── frontend
├── docker
└── docker-compose.yml
```

## Notes

- The `web` service exposes the app on port `8080`.
- The `frontend` service serves Vite on port `5173`.
- Default database credentials in Docker Compose:
	- Database: `laravel`
	- User: `laravel`
	- Password: `secret`
	- Root password: `root`