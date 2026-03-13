# Doctrack Phase 1 - Authentication Foundation

## Goal
Deliver a clean MVC starter with session authentication, shared layout rendering, and a unified dark-theme UI for Home/Login/Register.

## Scope
- Front controller routing via `public/index.php`
- Controller-based rendering flow (`controller -> view -> layout`)
- Shared layout template with injected page content
- Dark-themed public home page
- Dark-themed auth views (`login`, `register`)
- Register/login/logout using PHP sessions and bcrypt

## Routes
- `GET /` - home
- `GET /login` - login form
- `POST /login` - authenticate user
- `GET /register` - register form
- `POST /register` - create account
- `GET /dashboard` - auth-only placeholder screen
- `GET /logout` - clear session and redirect home

## Architecture Notes
- `app/Core/Router.php` provides explicit GET/POST route mapping.
- `app/Core/View.php` renders a view to output buffer, then injects it into `views/layout.php`.
- `app/Core/Controller.php` provides `render`, `redirect`, `flash`, and `keepOld` helpers.
- `app/Core/Database.php` provides a singleton PDO connection.
- `app/Core/Auth.php` wraps session login state.

## Styling
- Single stylesheet entrypoint: `public/css/main.css`
- `main.css` imports:
  - `theme.css`
  - `home.css`
  - `login.css`
  - `register.css`
- Layout includes only `main.css`.

## Validation Rules (Phase 1)
- Register:
  - Full name required (or first + last name combined)
  - Valid email format required
  - Password min length: 8
  - Password stored with `password_hash(..., PASSWORD_BCRYPT)`
- Login:
  - Email + password required
  - Credentials verified with `password_verify`

## Out of Scope
- CSRF tokens
- Password reset
- Email verification
- Role-based project permissions
- Documents and review workflow

## Next Phase
Projects and project membership (owner/reviewer/editor/viewer) with invitation flow.

