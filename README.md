# Nurse Management API 
### (Pr03 Proyecto Enfermeria)
 
## Description
Simple REST API to manage Nurse entities (create, read, update, basic login).  
Designed for development with Doctrine, includes helpers to import initial data from a JSON file for testing.

## Installation

1. Clone the repository
```bash
git clone <REPO_URL> /Users/soren/Documents/DAW2/Proyecto/Pr03_Proyecto_Enfermeria
cd /Users/soren/Documents/DAW2/Proyecto/Pr03_Proyecto_Enfermeria
```

2. Install dependencies
```bash
composer install
```

3. Configure the database (example: SQLite for dev)
Edit `.env` and set:
```env
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
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

## Notes & Best Practices
- Passwords are handled in plain text in this project for testing only. Use password_hash() and password_verify() and Symfony Security for production.
- When creating resources, returning HTTP 201 and a `Location` header pointing to the created resource is recommended.
- Validate input payloads (required fields, email format) before persisting.
- To inspect registered routes:
```bash
php bin/console debug:router
```
- Check logs at `var/log/dev.log` for server errors.

## Troubleshooting
- 404 errors: confirm server running and route correct.
- JSON parsing errors: ensure `Content-Type: application/json` and valid JSON body.
- DB errors: confirm `DATABASE_URL` and that migrations ran.


