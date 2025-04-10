# CEI-BRUSELAS School Platform

A school management platform under development for CEI Brussels using Laravel.

## Project Status

🚧 **Under Development** 🚧

This project is currently in active development phase, being built as a comprehensive school management system.

## Prerequisites

- PHP >= 8.1
- Composer
- MySQL
- Node.js & NPM

## Installation

1. Clone the repository:
```bash
git clone [repository_URL]
cd CEI-BRUSELAS
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure the database in .env file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations:
```bash
php artisan migrate
```

6. Start development server:
```bash
npm run dev
php artisan serve
```

## Features Under Development

- User Authentication System
- Student Management
  - Student profiles
  - Enrollment management
  - Academic records
- Course Management
  - Course scheduling
  - Curriculum planning
  - Assignment tracking
- Administrative Tools
  - Staff management
  - Resource allocation
  - Academic calendar
- Communication System
  - Internal messaging
  - Announcements
  - Parent-teacher communication

## Default Credentials

### Administrator
- Email: admin@ehb.be
- Password: Password!321

### Test User
- Email: user@example.com
- Password: user123

## Technologies Used

- Laravel 10
- PHP 8.1
- MySQL
- Blade Templates
- Tailwind CSS
- Alpine.js

## Project Structure

- `app/Http/Controllers/` - Application controllers
- `app/Models/` - Eloquent models
- `database/migrations/` - Database migrations
- `database/seeders/` - Seeders for test data
- `resources/views/` - Blade views
- `routes/` - Route definitions


