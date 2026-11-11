# Login MVC en PHP

Proyecto universitario desarrollado con **PHP puro** (sin frameworks), arquitectura **MVC**, **POO**, principios **SOLID**, **Composer** y autoload **PSR-4**.

---

## Funcionalidades

- **Login** con sesión, protección CSRF y cierre de sesión por POST.
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
│   ├── Core/            Router, Database, View, Session, Csrf, AuthMiddleware, RoleMiddleware
│   ├── Controllers/     Auth, Register, Dashboard, Admin y gestión de usuarios
│   ├── Models/          User, Role (enum)
│   ├── Repositories/    Interfaces y UserRepository, LoginAttemptRepository
│   ├── Services/        AuthService, RegisterService, RateLimiter, UserManagementService
│   ├── Exceptions/      ValidationException, TooManyAttemptsException
│   └── Views/           Plantillas HTML (layouts, auth, admin, dashboard, errors)
├── config/              Configuración (database.php)
├── database/            schema.sql, seed.php y migrations/
├── composer.json
└── .env.example
```

### Flujo de una petición

```
Navegador → public/index.php → Router → (Middleware) → Controller → Service → Repository → MySQL
                                                           ↓
                                                         View → HTML
```

### Responsabilidad de cada capa

| Capa | Clases | Responsabilidad |
|---|---|---|
| **Modelo** | `User`, `Role` | Datos y rol del usuario |
| **Vista** | `Views/*` y `View` | Mostrar información, sin decisiones |
| **Controlador** | `AuthController`, `RegisterController`, `DashboardController`, `AdminController`, `UserManagementController` | Coordinar petición y respuesta |
| **Servicio** | `AuthService`, `RegisterService`, `RateLimiter`, `UserManagementService` | Reglas de negocio de autenticación, registro y usuarios |
| **Repositorio** | `UserRepository`, `LoginAttemptRepository` | Persistencia mediante PDO |

---

## 3. Principios SOLID aplicados

| Principio | Dónde se aplica |
|---|---|
| **S** (Responsabilidad única) | `AuthService` verifica credenciales, `RateLimiter` controla intentos, `RegisterService` valida registros y `UserRepository` consulta la base. Los controladores coordinan HTTP. |
| **O** (Abierto/Cerrado) | `AuthMiddleware` y `RoleMiddleware` añaden protección de rutas sin modificar el `Router`. |
| **L** (Sustitución de Liskov) | Las dependencias por interfaz permiten sustituir implementaciones. No hay todavía una implementación en memoria ni una prueba automatizada que verifique esta sustitución. |
| **I** (Segregación de interfaces) | `UserRepositoryInterface` (lectura puntual), `UserWriterInterface` (escritura) y `UserListInterface` (listado) separan los contratos según el uso. |
| **D** (Inversión de dependencias) | Los servicios dependen de interfaces, no de MySQL. Las dependencias se inyectan desde `public/index.php`. |

---

## 4. Seguridad implementada

- Contraseñas con `password_hash()` y `password_verify()`.
- Consultas preparadas con PDO (`EMULATE_PREPARES` desactivado).
- Token **CSRF** en formularios, validado con `hash_equals()`.
- `session_regenerate_id(true)` tras el login (evita fijación de sesión).
- Cookie de sesión `HttpOnly` y `SameSite=Lax`.
- Logout por POST, con destrucción de sesión y cookie.
- Límite de intentos de login persistido en base de datos (no en sesión) y comprobado **antes** de verificar la contraseña.
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

Crear el usuario administrador de prueba (ejecutar una sola vez):

```bash
php database/seed.php
```

Iniciar el servidor:

```bash
php -S localhost:8000 -t public
```

Abrir `http://localhost:8000`. Credenciales de prueba: `admin@example.com` / `Admin1234`.

> **Migraciones:** `schema.sql` contiene el esquema para una instalación nueva, por lo que no es necesario ejecutar `database/migrations/`. Las migraciones actualizan bases de datos existentes de versiones anteriores y deben aplicarse en orden numérico.

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

- Es un **piloto**: no incluye recuperación de contraseña ni confirmación de correo electrónico.
- La autorización admin consulta el rol en la base de datos; cambiar el rol afecta el acceso a `/admin` en la siguiente petición protegida.
- El límite de intentos usa solo `REMOTE_ADDR`. Detrás de un proxy inverso habría que configurar la IP real de forma segura.
- Los intentos fallidos no se purgan automáticamente; se borran para esa combinación de correo + IP tras un login correcto.
- El registro puede revelar indirectamente si un correo existe; confirmarlo por correo ayudaría a reducir esa posibilidad.
- Las migraciones se aplican manualmente; no hay herramienta de migraciones.
- El usuario `root` sin contraseña y las credenciales de `seed.php` son solo para desarrollo local.
- Para Apache, configura el `DocumentRoot` en `public/`; si el servidor apunta a la raíz del proyecto, hará falta una regla de reescritura que dirija las peticiones a `public/index.php`.
- No hay pruebas automatizadas; los flujos se han verificado manualmente y con scripts temporales.
- En local no se usa HTTPS; en producción la cookie de sesión debe marcarse `Secure`.

