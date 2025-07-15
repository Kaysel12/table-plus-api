# 🗂️ Table Plus API - Laravel

Una API RESTful construida con **Laravel**, que permite la **gestión de tareas** con funcionalidades como:
- CRUD completo con validaciones por DTOs y Requests.
- Backup/Restauración en formato **XML**.
- **Integración SOAP** con servicios externos.
- **Caché con Redis**.
- Autenticación con **JWT (Passport)**.
- Arquitectura en **capas (DTO, Servicios, Repositorios, Excepciones)**.

---

## 📦 Requisitos

- PHP 8.2+
- Composer
- Laravel 12+
- PostgreSQL
- Redis (recomendado)
- Postman / Swagger para pruebas
- Extensiones PHP:
  - `curl`, `mbstring`, `xml`, `zip`, `pdo`, `openssl`

---

## ⚙️ Instalación del Proyecto

```bash
# Clona el repositorio
git clone https://github.com/tu-usuario/table-plus-api.git
cd table-plus-api

# Instala las dependencias de PHP
composer install

# Copia el archivo .env y configúralo
cp .env.example .env

# Genera la clave de la app
php artisan key:generate

# Configura la base de datos en el .env
# Luego ejecuta las migraciones y los seeders
php artisan migrate --seed

# Instala Passport para autenticación
php artisan passport:install

# (Opcional) Configura Redis si vas a usar caché
```

---

## 🚀 Ejecución

```bash
php artisan serve
```

API corriendo en: `http://localhost:8000`

---

## 📖 Documentación Swagger

La documentación de endpoints está disponible automáticamente gracias a **Swagger UI** con `l5-swagger`.

```bash
php artisan l5-swagger:generate
```

URL para ver documentación:  
👉 `http://localhost:8000/api/documentation`

---

## 🧪 Autenticación

```bash
# Login con email y password para obtener un JWT token
POST /api/auth
```

Añadir el token JWT en el header:  
`Authorization: Bearer <token>`

---

## 📝 Endpoints Principales

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | /api/tasks | Listar tareas con filtros y paginación |
| POST | /api/tasks | Crear tarea |
| PATCH | /api/tasks/{id} | Actualizar tarea |
| DELETE | /api/tasks/{id} | Eliminar tarea (soft delete) |
| GET | /api/tasks/export-xml | Descargar backup en XML |
| POST | /api/tasks/restore-xml | Restaurar tareas desde archivo XML |
| POST | /api/tasks/send-soap | Enviar tareas vía SOAP |

---

## 🛠️ Arquitectura

Este proyecto sigue una arquitectura limpia con división de responsabilidades:

- `App\Http\Controllers`: Controladores y validaciones de entrada.
- `App\Services`: Lógica de negocio.
- `App\DTO`: Transferencia de datos.
- `App\Repositories`: Acceso a datos (puedes extender aquí).
- `App\Exceptions`: Excepciones personalizadas.
- `App\Notifications`: Envío de recordatorios por correo.
- `App\Utils`: Utilidades como `ApiResponse`, `CacheHelper`, etc.

---

## ♻️ Caché

Se usa Redis con la clase `CacheHelper` para:
- Cachear listas de tareas por usuario.
- Limpiar cache al crear, actualizar o eliminar tareas.

---

## 📦 Backup y Restauración

- **Exportación:**  
  Descarga o envía un XML con las tareas del usuario.
- **Restauración:**  
  Sube un XML válido para restaurar tareas eliminadas o perdidas.

---

## 🔗 Integración SOAP

El sistema puede enviar tareas a un servicio externo SOAP (ficticio para pruebas).

```bash
POST /api/tasks/send-soap
```

> ⚠️ Usa un endpoint SOAP válido. Puedes probar con servicios públicos como:  
> https://www.dataaccess.com/webservicesserver/

---

## 📧 Envío por Email

Al exportar las tareas en XML, puedes optar por enviarlas al correo del usuario autenticado.

---

## ✅ TODOs o Extensiones Futuras

- Validación avanzada en restauración XML.
- Gestión de usuarios y roles.
- Sistema de notificaciones con cron.
- Interfaz Frontend (React/Vue).

---

## 🧑‍💻 Autor

Desarrollado por [Kaysel Núñez Abreu](https://github.com/tu-usuario)

---

## 📄 Licencia

Este proyecto está bajo la licencia MIT.
