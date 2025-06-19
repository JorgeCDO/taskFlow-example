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

## ⚙️ Instalación (Sin Docker)

```bash
#Clonar repositorio
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
```

## ⚙️ Instalación (Con Docker)
```bash
#Clonar repositorio
git clone git@github.com:JorgeCDO/taskFlow-example.git

#Haber instalado previamente Docker en tu equipo

#Ir a la raiz del proyecto mediante terminal

#Ejecutar el siguiente comando:
docker compose up --build

#Acceder
Acceder a la web mediante la url http://localhost:8000/

#Comando para dar de baja los servicios:
docker compose down -v
```
