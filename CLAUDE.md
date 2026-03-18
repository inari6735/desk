# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Symfony 8.0 web application (PHP >=8.4) with user authentication (form login + Google OAuth2), role-based permissions system, and an EasyAdmin admin panel. Uses PostgreSQL 16, Doctrine ORM, and Symfony AssetMapper for frontend assets.

## Development Commands

```bash
# Start infrastructure (PostgreSQL + Mailpit)
docker compose up -d

# Install dependencies
composer install

# Run database migrations
php bin/console doctrine:migrations:migrate

# Load fixtures (creates admin user: admin@example.com / admin)
php bin/console doctrine:fixtures:load

# Start dev server
symfony serve
# or
php -S localhost:8000 -t public/

# Run tests
php bin/phpunit

# Generate a new migration after entity changes
php bin/console doctrine:migrations:diff

# Clear cache
php bin/console cache:clear
```

## Architecture

### Authentication & Security

- **Form login** + **Google OAuth2** (via `knpuniversity/oauth2-client-bundle`)
- `GoogleAuthenticator` handles OAuth callback, creates or links users by Google ID/email
- Security config: `config/packages/security.yaml` — form_login is the entry point, Google authenticator is a custom authenticator
- `/admin` requires `ROLE_ADMIN`

### Roles & Permissions System

- **`Permission` enum** (`src/Enum/Permission.php`) — central registry of all permissions. Add new permissions here.
- **`Role` entity** — has a name and a JSON array of permission strings from the enum
- **User ↔ Role** — ManyToMany relationship. Users also keep a legacy `roles` array for Symfony's built-in `ROLE_*` system.
- **`PermissionVoter`** — enables `is_granted('PERMISSION_NAME')` checks in controllers and Twig
- Both roles and user-role assignments are managed via EasyAdmin at `/admin`

### Frontend

- **Symfony AssetMapper** with import maps (no Webpack/Vite)
- **Stimulus** controllers in `assets/controllers/`
- **Hotwire Turbo** for SPA-like navigation
- Styles in `assets/styles/app.css`

### Database

- PostgreSQL 16 via Docker Compose
- Doctrine ORM with PHP attribute mappings in `src/Entity/`
- UUID primary keys on all entities
- `User.password` is nullable (Google-only users may not have a password)

### Docker Services

- **PostgreSQL**: port 5432, credentials `app/!ChangeMe!`
- **Mailpit**: SMTP on 1025, web UI on 8025
