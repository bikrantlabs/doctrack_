# Doctrack (Phase 1)

Doctrack is a web-based document review and approval platform.

This phase includes:
- MVC front controller and routing
- Shared layout rendering
- Dark-themed Home/Login/Register pages
- Session auth (register/login/logout) using bcrypt and MySQL

## Requirements
- PHP 8.1+
- MySQL 8+
- Apache (XAMPP) or PHP built-in server

## Database
1. Create a database (default expected name: `doctrack`).
2. Run `schema.sql` exactly as provided.

Connection settings are read from environment variables (with defaults):
- `DB_HOST` (default `127.0.0.1`)
- `DB_PORT` (default `3306`)
- `DB_NAME` (default `doctrack`)
- `DB_USER` (default `root`)
- `DB_PASSWORD` (default empty)

## Local Run
Use one of the following options.

### 1) XAMPP Apache
Serve the `public` directory as the web root.

### 2) PHP Built-in Server
```bash
cd public
php -S 127.0.0.1:8099
```

Open:
- `http://127.0.0.1:8099/`
- `http://127.0.0.1:8099/login`
- `http://127.0.0.1:8099/register`

## Quick Checks
```bash
# Syntax check all PHP files
find . -name "*.php" -print0 | xargs -0 -n1 php -l
```

On Windows PowerShell:
```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

## Structure (Phase 1)
- `public/index.php` - front controller + route registration
- `app/Core` - Router, View, Controller, Auth, DB, helpers
- `app/Controllers` - Home and Auth controllers
- `views/layout.php` - shared HTML shell
- `views/index.php` - home content
- `views/auth/login.php` - login view
- `views/auth/register.php` - register view
- `views/dashboard.php` - temporary post-login page
- `public/css/main.css` - single stylesheet entrypoint

## Notes
- `schema.sql` remains unchanged.
- This is the starter foundation for upcoming project/document/review phases.

