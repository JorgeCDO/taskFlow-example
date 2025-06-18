# TaskFlow 🧩

**TaskFlow** es una API RESTful para la gestión de tareas colaborativas, desarrollada con Laravel y MySQL.

---

## 🚀 Requisitos

- PHP >= 8.1
- Composer
- MySQL o SQLite (opcional para testing)
- Laravel 10+
- Node.js y npm (opcional para documentación con Swagger UI)
- Postman

---

## ⚙️ Instalación

```bash
git clone git@github.com:JorgeCDO/taskFlow-example.git

# Instalar dependencias
composer install

# Copiar y configurar variables de entorno
cp .env.example .env

# Genera la clave de aplicación
php artisan key:generate

# Configura tu conexión a la base de datos en .env
DB_DATABASE=taskflow
DB_USERNAME=root
DB_PASSWORD=

# Ejecuta migraciones
php artisan migrate

# Instalar Swagger UI
npm install
php artisan serve
