# Authentication Setup

## Overview
A complete authentication system has been implemented with login, registration, and access control.

## Features
- ✅ User registration and login
- ✅ Password hashing with PHP's `password_hash()`
- ✅ Session-based authentication
- ✅ Route protection middleware
- ✅ User dropdown with logout
- ✅ HTMX-compatible redirects
- ✅ Doctrine ORM integration

## Database Schema
The system creates two main tables:
- `users` - User accounts with email, password, and name
- `traffic_logs` - Traffic tracking data (if implemented)

## Default Admin User
A default administrator account is created:
- **Email:** admin@example.com
- **Password:** admin123

## Routes

### Public Routes
- `GET /` - Home page (accessible to everyone)
- `GET /login` - Login form
- `POST /login` - Process login
- `GET /register` - Registration form
- `POST /register` - Process registration

### Protected Routes (require authentication)
- `GET /dashboard` - Dashboard (redirects to login if not authenticated)
- `GET /dashboard/chart` - Chart data
- `GET /dashboard/table` - Table data
- `POST /logout` - Logout

## Usage

### 1. Setup Database
```bash
php create-schema.php
```

### 2. Start Server
```bash
php -S localhost:8080 -t public
```

### 3. Access Application
- Visit `http://localhost:8080`
- Click "Login" to access protected areas
- Use the default admin credentials or register a new account

## Components

### Controllers
- `AuthController` - Handles login, registration, and logout
- `DashboardController` - Protected dashboard functionality
- `HomeController` - Public home page

### Middleware
- `AuthMiddleware::requireAuth()` - Protects routes requiring login
- `AuthMiddleware::requireGuest()` - Redirects authenticated users

### Services
- `AuthService` - Manages authentication state and user data

### Entities
- `User` - User model with Doctrine ORM mapping

### Views
- `auth/login.blade.php` - Login form
- `auth/register.blade.php` - Registration form
- Responsive navigation with user dropdown

## Security Features
- Passwords are hashed using `password_hash()` with default algorithm
- Session-based authentication
- CSRF protection through HTMX forms
- Input validation on registration
- Unique email constraint

## Customization
- Modify `AuthService` to add role-based permissions
- Update views in `app/Views/auth/` for custom styling
- Add password reset functionality
- Implement "remember me" functionality
