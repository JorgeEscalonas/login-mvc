# Login MVC en PHP

Proyecto universitario desarrollado con **PHP puro** (sin frameworks), arquitectura **MVC**, **POO**, principios **SOLID**, **Composer** y autoload **PSR-4**.

---

## Funcionalidades

- **Login** con sesión, protección CSRF y cierre de sesión por POST.
- **AJAX** en formularios y navegación interna para actualizar la página sin recarga; enlaces externos mantienen su comportamiento normal.
- **Recuperación local de contraseña** en tres pasos con token temporal de un solo uso.
- **Registro público** con validación; las cuentas creadas desde el formulario reciben siempre el rol `user`. El administrador puede asignar roles desde el panel.
- **Límite de intentos de login:** se cuentan los fallos de los últimos 15 minutos por combinación de correo + IP. Al alcanzar 5, los nuevos intentos reciben HTTP 429 mientras haya 5 fallos en esa ventana móvil; la respuesta incluye `Retry-After: 900`.
- **Roles y permisos:** `admin` y `user`. `/admin` solo permite administradores; usuarios autenticados sin permiso reciben HTTP 403 y visitantes no autenticados son redirigidos al login.
- **Administración de usuarios:** listado, creación, edición y eliminación desde el panel admin.

---

## 1. Tecnologías y justificación

| Tecnología | Uso | Justificación |
|---|---|---|
| **PHP 8.2** | Lenguaje principal | Tipado estricto, `readonly`, promoción de propiedades en constructores y tipo `never`. |
| **MySQL (MariaDB)** | Base de datos | Persistencia de usuarios. Se administra con XAMPP y phpMyAdmin. |
| **PDO** | Acceso a datos | Consultas preparadas reales: protección contra inyección SQL. |
| **HTML5 y CSS** | Vistas | Sin frameworks, para centrar el aprendizaje en la arquitectura. |
| **Composer** | Gestor de dependencias | Instala librerías y genera el autoload. |
| **vlucas/phpdotenv** | Configuración | Mantiene las credenciales fuera del código y de Git (`.env`). Única dependencia externa, a propósito. |
| **Git y GitHub** | Control de versiones | Historial, ramas y publicación. |

El router, las vistas, la sesión y el CSRF se implementaron a mano para comprender cómo funcionan por dentro.

---

## 2. Arquitectura

```
login-mvc/
├── public/              Única carpeta expuesta al navegador
│   ├── index.php        Front Controller y composition root
│   └── css/style.css
├── src/                 Namespace App\ (PSR-4)
│   ├── Core/            Router, Database, View, Session, Csrf, RateLimiter y middleware
│   ├── Controllers/     Auth, Register, PasswordReset, Dashboard, Admin y gestión de usuarios
│   ├── Models/          User, Role (enum)
│   ├── Exceptions/      ValidationException, TooManyAttemptsException
│   └── Views/           Plantillas HTML (layouts, auth, admin, dashboard, errors)
├── config/              Configuración (database.php)
├── database/            schema.sql y seed.php
├── composer.json
└── .env.example
```

### Flujo de una petición

```
Navegador → public/index.php → Router → (Middleware) → Controller → Model → MySQL
                                                        ↓
                                                       View → HTML
```

### Responsabilidad de cada capa

| Capa | Clases | Responsabilidad |
|---|---|---|
| **Modelo** | `User`, `Role` | Datos, reglas de usuario y acceso a sus registros mediante PDO |
| **Vista** | `Views/*` y `View` | Mostrar información, sin decisiones |
| **Controlador** | `AuthController`, `RegisterController`, `PasswordResetController`, `DashboardController`, `AdminController`, `UserManagementController` | Coordinar petición, respuesta, sesión y vista |
| **Infraestructura** | `Database`, `Router`, `Session`, `Csrf`, `RateLimiter` | Acceso técnico y protección de peticiones |

---

## 3. Principios SOLID aplicados

| Principio | Dónde se aplica |
|---|---|
| **S** (Responsabilidad única) | `User` contiene datos y reglas de usuario; los controladores coordinan HTTP y `RateLimiter` controla intentos en sesión. |
| **O** (Abierto/Cerrado) | `AuthMiddleware` y `RoleMiddleware` añaden protección de rutas sin modificar el `Router`. |
| **L** (Sustitución de Liskov) | Las clases se mantienen compatibles con sus tipos públicos y pueden ampliarse sin modificar las llamadas existentes. |
| **I** (Segregación de interfaces) | Se evitan interfaces innecesarias: cada clase expone únicamente las operaciones usadas por el proyecto. |
| **D** (Inversión de dependencias) | Las clases técnicas se pasan desde `public/index.php`; no se usan puertos, adaptadores ni interfaces internas de aplicación. |

---

## 4. Seguridad implementada

- Contraseñas con `password_hash()` y `password_verify()`.
- Consultas preparadas con PDO (`EMULATE_PREPARES` desactivado).
- Token **CSRF** en formularios, validado con `hash_equals()`.
- `session_regenerate_id(true)` tras el login (evita fijación de sesión).
- Cookie de sesión `HttpOnly` y `SameSite=Lax`.
- Logout por POST, con destrucción de sesión y cookie.
- Límite de intentos de login almacenado en sesión, comprobado **antes** de verificar la contraseña y reiniciado después de un login correcto.
- Recuperación local en tres pasos: solicitar token, verificarlo y elegir contraseña. El token aleatorio se guarda como hash, dura 15 minutos y solo se escribe en la terminal con `APP_ENV=development` y acceso desde localhost. Al terminar, vuelve al login; una nueva solicitud invalida el token anterior. No sustituye el envío de correo en producción.
- El registro público asigna `Role::User` en el servidor; no acepta un rol enviado por el formulario.
- Autorización con `RoleMiddleware`: consulta el rol actual en base de datos, deniega por defecto y devuelve HTTP 403 a usuarios autenticados sin permiso.
- Las contraseñas nuevas tienen un máximo de 72 bytes, el límite considerado por bcrypt.
- La respuesta para correos duplicados es genérica para dificultar la enumeración; no elimina por completo todas las diferencias observables del registro.
- Escape de salida con `htmlspecialchars()` (evita XSS).
- Mensaje de error genérico y mitigación de *timing attacks*.
- Cabeceras: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy`.
- Solo `public/` es accesible desde el navegador; `.env` y `vendor/` quedan fuera.

---

## 5. Instalación

**Requisitos:** PHP 8.2 o superior (extensiones `pdo_mysql`, `mbstring` y `openssl`), Composer, MySQL/MariaDB y Git.

```bash
git clone https://github.com/JorgeEscalonas/login-mvc.git
cd login-mvc
composer install
cp .env.example .env
```

Crear la base de datos y todas sus tablas (con MySQL iniciado):

```bash
mysql -u root < database/schema.sql
```

Para agregar la tabla de recuperación a una base existente:

```bash
mysql -u root < database/password_reset.sql
```

Crear el usuario administrador de prueba (ejecutar una sola vez):

```bash
php database/seed.php
```

Iniciar el servidor:

```bash
php -S localhost:8000 -t public
```

Abrir `http://localhost:8000`. Credenciales de prueba: `admin@example.com` / `Admin1234`.

> **Base de datos:** ejecutar `schema.sql` en una instalación nueva, o `password_reset.sql` si la base ya existía; ejecutar `seed.php` para crear los datos iniciales.

---

## 6. Comandos de Composer utilizados

| Comando | Qué hace |
|---|---|
| `composer init` | Asistente que genera `composer.json`. |
| `composer require vlucas/phpdotenv` | Instala la librería y la registra en `composer.json`. |
| `composer dump-autoload` | Regenera el autoload tras cambiar las reglas PSR-4. |
| `composer install` | Instala las versiones exactas de `composer.lock`. |

**PSR-4:** `"App\\": "src/"` hace que `App\Core\Router` se cargue desde `src/Core/Router.php`.

---

## 7. Flujo de trabajo con Git

- `main`: versiones estables.
- `develop`: desarrollo diario.
- Commits con formato `tipo: descripción` (`feat`, `fix`, `chore`, `docs`).

---

## 8. Limitaciones conocidas

- Es un **piloto**: la recuperación funciona solo en desarrollo local y no incluye confirmación de correo electrónico.
- La autorización admin consulta el rol en la base de datos; cambiar el rol afecta el acceso a `/admin` en la siguiente petición protegida.
- El límite de intentos usa solo `REMOTE_ADDR`. Detrás de un proxy inverso habría que configurar la IP real de forma segura.
- Los intentos fallidos no se purgan automáticamente; se borran para esa combinación de correo + IP tras un login correcto.
- El registro puede revelar indirectamente si un correo existe; confirmarlo por correo ayudaría a reducir esa posibilidad.
- Las migraciones se aplican manualmente; no hay herramienta de migraciones.
- El usuario `root` sin contraseña y las credenciales de `seed.php` son solo para desarrollo local.
- Para Apache, configura el `DocumentRoot` en `public/`; si el servidor apunta a la raíz del proyecto, hará falta una regla de reescritura que dirija las peticiones a `public/index.php`.
- No hay pruebas automatizadas; los flujos se han verificado manualmente y con scripts temporales.
- En local no se usa HTTPS; en producción la cookie de sesión debe marcarse `Secure`.

