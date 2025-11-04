# 🏥 Proyecto de Gestión de Enfermería

## 📋 Propósito
Este proyecto es una aplicación web desarrollada con **Symfony** y **Doctrine ORM** que permite gestionar enfermeros, pacientes y turnos.  
Incluye un CRUD completo (crear, leer, actualizar y eliminar) para los recursos principales y pruebas automatizadas con **PHPUnit**.

---

## ⚙️ Instalación

### Requisitos previos
- PHP >= 8.1  
- Composer  
- MySQL o MariaDB  
- Symfony CLI (opcional, pero recomendado)

### Pasos de instalación

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/tuusuario/tu-repositorio.git
   cd tu-repositorio
   ```

2. Instalar dependencias:
   ```bash
   composer install
   ```

3. Configurar el entorno:
   Copiar el archivo `.env` y configurar la base de datos:
   ```bash
   cp .env .env.local
   ```
   Luego editar la línea de conexión:
   ```
   DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/nombre_base_datos"
   ```

4. Crear la base de datos y ejecutar migraciones:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. Iniciar el servidor:
   ```bash
   symfony serve
   ```
   o
   ```bash
   php -S localhost:8000 -t public
   ```

---

## 🧪 Ejecución de Tests
Para ejecutar las pruebas con **PHPUnit**:
```bash
php bin/phpunit
```

Los tests están ubicados en la carpeta `tests/` e incluyen pruebas del controlador principal (`NurseControllerTest`).

---

## 🧰 Uso del Proyecto
### Endpoints principales (API)
| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/nurse/index` | Listar enfermeros |
| `GET` | `/nurse/{id}` | Ver detalle de un enfermero |
| `POST` | `/nurse/new` | Crear enfermero |
| `PUT` | `/nurse/{id}` | Actualizar enfermero |
| `DELETE` | `/nurse/{id}` | Eliminar enfermero |

---

## 👩‍💻 Tecnologías utilizadas
- Symfony 6 / 7  
- Doctrine ORM  
- Twig  
- PHPUnit  
- Bootstrap 5  

---

## 📄 Licencia
Este proyecto se distribuye bajo la licencia MIT.
