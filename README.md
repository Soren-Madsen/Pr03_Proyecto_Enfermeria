# 🏥 Proyecto Enfermería – Symfony + Doctrine + PHPUnit + GitHub Actions CI - *Cat Clean*

## 📋 Propósito
**Cat Clean** presenta una aplicación web desarrollada con **Symfony** y **Doctrine ORM** que permite gestionar enfermeros.
El sistema incluye un **CRUD completo** (`NurseController`) (crear, leer, actualizar y eliminar) para los recursos principales, además de pruebas automatizadas con **PHPUnit** y un flujo de **Integración Continua (CI)** mediante **GitHub Actions**.

---

## ⚙️ Instalación

### 🔧 Requisitos previos
- PHP >= 8.3  
- Composer  
- Symfony CLI (opcional pero recomendado)  
- SQLite (para entorno de test) o MySQL / MariaDB (para desarrollo)  
- XAMPP o similar (para Apache/MySQL)
- Extensiones PHP activas:
openssl
mbstring
tokenizer
xml
xmlwriter
dom
libxml
pdo_mysql
intl
curl
fileinfo
> 💡 Puedes verificarlas con `php -m`.
---

### 🚀 Pasos de instalación

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/Soren-Madsen/Pr03_Proyecto_Enfermeria.git
   cd Pr03_Proyecto_Enfermeria
   ```
   Esto instalará todas las librerías necesarias (Symfony, Doctrine, PHPUnit, etc.).

2. **Instalar dependencias:**
   ```bash
   composer install
   ```

3. **Configurar el entorno:**
   .env
   Contiene las variables del entorno de desarrollo.

   .env.test
   Entorno de test utilizado por PHPUnit.
   Ejemplo recomendado:
   APP_ENV=test
   APP_DEBUG=1
   APP_SECRET='$ecretf0rt3st'
   DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
   KERNEL_CLASS='App\Kernel'

   Usa SQLite para las pruebas: no afecta tu base de datos MySQL local.
   
   Si quieres usar una BBDD local - Copia el archivo `.env` y crea un archivo local:
   ```bash
   cp .env .env.local
   ```
   Luego, edita la variable de entorno de la base de datos:
   ```
   DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/nombre_base_datos"
   ```

4. **Crear base de datos y ejecutar migraciones:**
    Si usas MySQL en desarrollo, crea tu base de datos y aplica migraciones:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```
    Para test (si usas SQLite):
    php bin/console doctrine:database:create --env=test
    php bin/console doctrine:migrations:migrate --env=test

5. **Iniciar el servidor:**
   ```bash
   symfony serve
   ```
   o
   ```bash
   php -S localhost:8000 -t public
   ```

---

## 🧪 Ejecución de Tests

El proyecto incluye pruebas funcionales y de integración con **PHPUnit**.  
Ejemplo de prueba: `tests/Controller/NurseControllerTest.php`.

Para ejecutar todos los tests:
```bash
php bin/phpunit
```
O para ver más detalles:
```bash
vendor/bin/phpunit --testdox
```

Los tests usan una base de datos **SQLite en memoria**, por lo que no modifican tus datos reales:
```
DATABASE_URL=sqlite:///:memory:
```

---

## 🤖 Integración Continua (CI) con GitHub Actions

El proyecto incluye un flujo automático en `.github/workflows/phpunit.yml`  
que ejecuta los tests cada vez que se realizan cambios en el repositorio.

### 🔹 1. Nombre del Workflow
```yaml
name: Run PHPUnit testsComplete
```
Es el nombre que aparecerá en la pestaña **Actions** de GitHub.

---

### 🔹 2. Cuándo se ejecuta
```yaml
on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]
```
Se ejecuta automáticamente cuando:
- Haces un **push** a la rama `main`.
- Creas una **pull request** hacia `main`.

💡 Esto garantiza que ningún cambio se fusione sin pasar las pruebas.

---

### 🔹 3. Configuración del Job
```yaml
jobs:
  test:
    runs-on: ubuntu-latest
```
Crea un job llamado `test` que se ejecuta en una máquina virtual de **Ubuntu**.

---

### 🔹 4. Estrategia con Matrix
```yaml
strategy:
  fail-fast: false
  matrix:
    php: ['8.3', '8.4']
```
- Ejecuta el workflow con **dos versiones de PHP** (8.3 y 8.4).  
- `fail-fast: false` permite continuar las pruebas en todas las versiones incluso si una falla.

---

### 🔹 5. Instalación y ejecución
1. **Descargar código del repositorio:**
   ```yaml
   - name: Checkout code
     uses: actions/checkout@v4
   ```

2. **Configurar PHP con extensiones necesarias:**
   ```yaml
   - name: Set up PHP ${{ matrix.php }}
     uses: shivammathur/setup-php@v2
     with:
       php-version: ${{ matrix.php }}
       extensions: mbstring, xml, ctype, iconv, intl, pdo_sqlite, dom, json, tokenizer
       coverage: none
   ```

3. **Instalar dependencias de Composer:**
   ```yaml
   - name: Install Composer dependencies
     run: composer install --no-progress --no-suggest --prefer-dist
   ```

4. **Preparar entorno de test:**
   ```yaml
   - name: Prepare test environment
     run: |
       mkdir -p var/cache/test var/log
       php bin/console cache:clear --env=test
   ```

5. **Ejecutar PHPUnit:**
   ```yaml
   - name: Run PHPUnit
     env:
       APP_ENV: test
       DATABASE_URL: sqlite:///:memory:
     run: vendor/bin/phpunit --testdox
   ```

🧠 Resultado:
- Los tests se ejecutan automáticamente al hacer *push* o *pull request*.  
- Se prueban las versiones PHP 8.3 y 8.4.  
- Cualquier error se detecta antes del merge.

---

## 🧰 Uso del Proyecto

### 🩺 Endpoints principales (API)
| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/nurse/index` | Listar todos los enfermeros |
| `GET` | `/nurse/id/{id}` | Buscar enfermero por ID |
| `GET` | `/nurse/name/{name}` | Buscar enfermero por nombre |
| `POST` | `/nurse/new` | Crear un nuevo enfermero |
| `PUT` | `/nurse/id/{id}` | Actualizar un enfermero existente |
| `DELETE` | `/nurse/id/{id}` | Eliminar un enfermero |

---

## 👩‍💻 Tecnologías utilizadas
- Symfony 6 / 7  
- Doctrine ORM  
- PHPUnit  
- Twig  
- Bootstrap 5  
- GitHub Actions  

---

## 🧑‍🎓 Autor
**Cat Clean**  
📦 Repositorio: [https://github.com/Soren-Madsen/Pr03_Proyecto_Enfermeria.git](https://github.com/Soren-Madsen/Pr03_Proyecto_Enfermeria.git)

---

## 📄 Licencia
Distribuido bajo la licencia **MIT**.  
Eres libre de usar, modificar y compartir este proyecto respetando los términos de la licencia.
