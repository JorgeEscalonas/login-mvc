# Login MVC en PHP

Proyecto universitario desarrollado con **PHP puro** (sin frameworks), arquitectura **MVC**, **POO**, principios **SOLID**, **Composer** y autoload **PSR-4**.

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
│   ├── Core/            Router, Database, View, Session, Csrf, AuthMiddleware
│   ├── Controllers/     AuthController, DashboardController
│   ├── Models/          User
│   ├── Repositories/    UserRepositoryInterface, UserRepository
│   ├── Services/        AuthService
│   └── Views/           Plantillas HTML (layouts, auth, dashboard)
├── config/              Configuración (database.php)
├── database/            schema.sql y seed.php
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
| **Modelo** | `User`, `UserRepository` | Datos y acceso a ellos |
| **Vista** | `Views/*` y `View` | Mostrar información, sin decisiones |
| **Controlador** | `AuthController`, `DashboardController` | Coordinar petición y respuesta |
| **Servicio** | `AuthService` | Regla de negocio del login |

---

## 3. Principios SOLID aplicados

| Principio | Dónde se aplica |
|---|---|
| **S** (Responsabilidad única) | `AuthService` valida credenciales, `AuthController` coordina, `UserRepository` consulta, `Session` maneja la sesión. |
| **O** (Abierto/Cerrado) | `AuthMiddleware` añade protección de rutas sin modificar el `Router`. |
| **L** (Sustitución de Liskov) | Un repositorio en memoria sustituyó al de MySQL en las pruebas sin cambiar `AuthService`. |
| **I** (Segregación de interfaces) | `UserRepositoryInterface` declara solo `findByEmail`, lo único que el login necesita. |
| **D** (Inversión de dependencias) | `AuthService` depende de `UserRepositoryInterface`, no de MySQL. Las dependencias se inyectan desde `public/index.php`. |

---

## 4. Seguridad implementada

- Contraseñas con `password_hash()` y `password_verify()`.
- Consultas preparadas con PDO (`EMULATE_PREPARES` desactivado).
- Token **CSRF** en formularios, validado con `hash_equals()`.
- `session_regenerate_id(true)` tras el login (evita fijación de sesión).
- Cookie de sesión `HttpOnly` y `SameSite=Lax`.
- Logout por POST, con destrucción de sesión y cookie.
- Escape de salida con `htmlspecialchars()` (evita XSS).
- Mensaje de error genérico y mitigación de *timing attacks*.
- Cabeceras: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Content-Security-Policy`.
- Solo `public/` es accesible desde el navegador; `.env` y `vendor/` quedan fuera.

---

## 5. Instalación

**Requisitos:** PHP 8.2 o superior (extensiones `pdo_mysql`, `mbstring`, `openssl`), Composer, MySQL/MariaDB y Git.

```bash
git clone https://github.com/JorgeEscalonas/login-mvc.git
cd login-mvc
composer install
cp .env.example .env
```

Crear la base de datos (con MySQL iniciado):

```bash
mysql -u root < database/schema.sql
```

Crear el usuario de prueba:

```bash
php database/seed.php
```

Iniciar el servidor:

```bash
php -S localhost:8000 -t public
```

Abrir `http://localhost:8000`. Credenciales de prueba: `admin@example.com` / `Admin1234`.

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


