# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Package Overview

This is a Laravel authentication and authorization package (`kobesoft/green-auth-core`) with Filament admin panel integration. It provides a complete user management system with multi-guard support, hierarchical groups, roles, and permissions.

## Common Commands

### Testing
```bash
# Run all tests
vendor/bin/pest

# Run specific test file
vendor/bin/pest tests/Feature/ExampleTest.php

# Run with coverage
vendor/bin/pest --coverage
```

### Development Setup
```bash
# Install dependencies
composer install

# Update autoload
composer dump-autoload
```

### Package Installation (in Laravel app)
```bash
# Interactive installation
php artisan green-auth:install

# Skip specific steps
php artisan green-auth:install --skip-config --skip-migrations
```

## Architecture

### Core Structure
- **Namespace**: `Green\Auth\`
- **Service Provider**: `GreenAuthServiceProvider` - registers config, views, translations, commands
- **Filament Plugin**: `GreenAuthPlugin` - integrates with Filament admin panels
- **Permission System**: `PermissionManager` facade manages hierarchical permissions

### Key Components

1. **Models** (`src/Models/`)
   - Base models: User, Group, Role, LoginLog
   - Traits in `Concerns/`: HasGroups, HasRoles, HasPermissions, etc.
   - Multi-guard support through dynamic model resolution

2. **Filament Integration** (`src/Filament/`)
   - Resources: UserResource, GroupResource, RoleResource (base classes)
   - Pages: Login, PasswordChange, PasswordExpired
   - Actions: User/Group/Role management actions
   - Custom table columns for permissions

3. **Permission System** (`src/Permission/`)
   - `PermissionManager`: Central permission registry
   - `Super`: Built-in super admin permission
   - Hierarchical permission structure with dot notation

4. **Password Management** (`src/Password/`)
   - `PasswordGenerator`: Generates complex passwords
   - `PasswordComplexity`: Validates password requirements
   - Configurable complexity rules and expiration

### Configuration

Main config file: `config/green-auth.php`
- Guards configuration (models, tables, settings per guard)
- Authentication settings (email/username login)
- User permissions (allow multiple groups/roles)
- Password settings (complexity, expiration)

### Multi-language Support
- Languages: English (`lang/en/`) and Japanese (`lang/ja/`)
- Translation keys: `green-auth::*`

### Publishing Resources
```bash
# Config only
php artisan vendor:publish --tag=green-auth-config

# All resources
php artisan vendor:publish --provider="Green\Auth\GreenAuthServiceProvider"
```

## Development Guidelines

### Model Extension
When extending base models, ensure to:
1. Use appropriate traits from `Models\Concerns\`
2. Implement required interfaces (HasFilamentAvatar, etc.)
3. Configure table names in config file

### Adding Permissions
Register new permission classes with:
```php
PermissionManager::register([YourPermissionClass::class]);
```

### Filament Resources
Base resources (UserResource, GroupResource, RoleResource) should be extended in the application, not modified directly.

### Testing
- Framework: Pest PHP with Laravel plugin
- Test namespace: `Green\Auth\Tests\`
- Use Orchestra Testbench for package testing
