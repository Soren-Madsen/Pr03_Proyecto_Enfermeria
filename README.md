# Nurse Management API 
### (Pr03 Proyecto Enfermeria)
 
## Description
Simple REST API to manage Nurse entities (create, read, update, basic login).  
Designed for development with Doctrine, includes helpers to import initial data from a JSON file for testing.

## Installation

1. Clone the repository
```bash
git clone https://github.com/Soren-Madsen/Pr03_Proyecto_Enfermeria.git
```

2. Install dependencies
```bash
composer install
```

3. Configure the database
Edit `.env` and set:
```env
DATABASE_URL="mysql://Nurse_forgotfuel:8d28f3d808de61576589f87613701945f3418ec0@5sjuec.h.filess.io:61001/Nurse_forgotfuel"
```

4. Install Doctrine & tools (if not present)
```bash
composer require symfony/orm-pack
composer require doctrine/doctrine-migrations-bundle
composer require --dev symfony/maker-bundle
```

5. Create DB and run migrations
```bash
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

6. (Optional) Import nurses from JSON
- Place your JSON (e.g. `src/json/nurses.json` or `data/nurses.json`).
- Create a simple import script `scripts/import_nurses.php` that boots the kernel and persists Nurse entities, then run:
```bash
php scripts/import_nurses.php
```

## Run (development)
Start the Symfony local server:
```bash
symfony serve
or
php -S 127.0.0.1:8000 -t public
```

## API Endpoints

Base URL: `http://localhost:8000`

- GET /nurse/name/{name}  
  - Find nurse by name 
  - Example:
    ```bash
    curl -X GET "http://localhost:8000/nurse/name/Ana"
    ```

- GET /nurse/index  
  - List all nurses.  
  - Example:
    ```bash
    curl -X GET "http://localhost:8000/nurse/index"
    ```

- POST /nurse/new  
  - Create a new nurse. Expects JSON body: `{ "name": "...", "email": "...", "password": "..." }`  
  - Returns 201 Created with JSON `{ "id": ..., "message": "Nurse created" }` or 400 on error.  
  - Example:
    ```bash
    curl -X POST "http://localhost:8000/nurse/new" \
      -H "Content-Type: application/json" \
      -d '{"name":"Ana","email":"a@x.com","password":"pwd"}'
    ```

- POST /nurse/login  
  - Simple login: accepts form-data or JSON `{ "email": "...", "password": "..." }`. Returns 200 on success, 401 otherwise.  
  - Example:
    ```bash
    curl -X POST "http://localhost:8000/nurse/login" \
      -H "Content-Type: application/json" \
      -d '{"email":"a@x.com","password":"pwd"}'
    ```

- GET /nurse/id/{id}  
  - Get nurse by ID. Example:
    ```bash
    curl -X GET "http://localhost:8000/nurse/id/1"
    ```

- PUT /nurse/id/{id}  
  - Update nurse fields (JSON body with any of `name`, `email`, `password`). Example:
    ```bash
    curl -X PUT "http://localhost:8000/nurse/id/1" \
      -H "Content-Type: application/json" \
      -d '{"name":"New Name"}'
    ```

## Troubleshooting
- 404 errors: confirm server running and route correct.
- JSON parsing errors: ensure `Content-Type: application/json` and valid JSON body.
- DB errors: confirm `DATABASE_URL` and that migrations ran.

## Continuous Integration (CI)

This repository includes a GitHub Actions workflow to run the PHPUnit test-suite on pushes and pull requests to `main`.

- Workflow: `.github/workflows/phpunit.yml` — sets up PHP 8.3, installs Composer dependencies and runs `vendor/bin/phpunit` using `phpunit.dist.xml`.

Note: Running the full test-suite locally requires dev dependencies compatible with your local PHP version. Currently the project uses PHPUnit 12 which needs PHP >= 8.3. If your local PHP is older (for example 8.2), `composer install` will fail. To run tests locally either:

1. Upgrade your local PHP to >= 8.3, then run:
```bash
composer install --no-interaction --prefer-dist
vendor/bin/phpunit --configuration phpunit.dist.xml --testdox
```

or

2. Adjust the development dependencies in `composer.json` (for example pin an older PHPUnit compatible with your PHP) — note this may diverge from CI.


