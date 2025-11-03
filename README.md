# 🏥 Proyecto Enfermería – Symfony + Doctrine + PHPUnit + GitHub Actions CI

Este proyecto implementa un backend en **Symfony** con **Doctrine ORM**, incluyendo controladores CRUD (por ejemplo, `NurseController`) y un entorno de pruebas automatizado con **PHPUnit** y **GitHub Actions**.

---

## ⚙️ Requisitos previos

Asegúrate de tener instalado:

- PHP **≥ 8.2**
- Composer
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

## 🧩 Instalación del proyecto

### 1️⃣ Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/tu-repo.git
cd tu-repo

2 Instalar dependencias
composer install
Esto instalará todas las librerías necesarias (Symfony, Doctrine, PHPUnit, etc.).

🧪 Entornos de ejecución
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

🧱 Usa SQLite para las pruebas: no afecta tu base de datos MySQL local.

🧠 Base de datos

Si usas MySQL en desarrollo, crea tu base de datos y aplica migraciones:
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

Para test (si usas SQLite):
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test

🧩 Ejecutar tests

Para ejecutar los tests de PHPUnit:
vendor/bin/phpunit

O para ver más detalles:
vendor/bin/phpunit --testdox


🤖 Integración continua (CI)
Este proyecto incluye configuración para GitHub Actions, que ejecuta automáticamente las pruebas cada vez que haces un push o un Pull Request.

Ruta del workflow:

bash
Copiar código
.github/workflows/phpunit.yml

Contenido base:
name: Run PHPUnit tests

on:
  push:
    branches: [ main, develop, "feature/**" ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, xml, ctype, iconv, intl, pdo_mysql, dom, json, tokenizer
          coverage: none

      - name: Install dependencies
        run: composer install --no-progress --no-suggest --prefer-dist

      - name: Run PHPUnit
        run: vendor/bin/phpunit

🔄 Proceso de integración continua

Commit + Push de tus cambios.

Pull Request a la rama principal (main o develop).

GitHub ejecutará automáticamente los tests en la pestaña Actions.

Si todos los tests pasan ✅, el pipeline muestra “Success”.

Si introduces un error en el código 🧨, el pipeline falla con “Failed”, indicando que las pruebas lo han detectado correctamente.

🧭 Preparar un nuevo equipo

Si abres el proyecto en otro ordenador:

Clona el repositorio:
git clone https://github.com/tu-usuario/tu-repo.git

Instala dependencias:
composer install

Asegúrate de tener las extensiones PHP activas.

Configura .env y .env.test si no existen.

(Opcional) Crea la base de datos si usas MySQL:
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

Ejecuta los tests:
vendor/bin/phpunit

👩‍⚕️ Autor

Proyecto realizado como parte del módulo de desarrollo backend en Symfony (DAW2).

---

¿Quieres que te añada dentro del README una **sección final de “Solución de errores comunes”** (por ejemplo: problemas con extensiones PHP o conflictos de versión con PHPUnit)?
