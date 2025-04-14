# CEI-BRUSELAS

## 📝 Beschrijving / Descripción
Dit is een Laravel-project voor het Centrum Evangelístico Internacional in Bruselas. Het project omvat een volledig functioneel webplatform met verschillende modules voor het beheer van studenten, professoren, vakken en gebruikers.

Este es un proyecto Laravel para el Centro Evangelístico Internacional en Bruselas. El proyecto incluye una plataforma web completamente funcional con varios módulos para la gestión de estudiantes, profesores, materias y usuarios.

## 🚀 Functies / Características
- Gebruikersbeheer (admin, professor, student) / Gestión de usuarios (admin, profesor, estudiante)
- Vakkenbeheer / Gestión de materias
- Relaties tussen studenten en professoren / Relaciones entre estudiantes y profesores
- Contactformulier / Formulario de contacto
- FAQ-systeem / Sistema de FAQ
- Dashboard voor cursussen / Dashboard para cursos

## 🛠️ Technische vereisten / Requisitos técnicos
- PHP 8.1 of hoger / PHP 8.1 o superior
- Composer
- MySQL
- Node.js en NPM
- Laravel 10.x

## 🚀 Installatie / Instalación

### 1. Kloon de repository / Clona el repositorio
```bash
git clone https://github.com/uw-gebruikersnaam/CEI-BRUSELAS.git
cd CEI-BRUSELAS
```

### 2. Installeer PHP-afhankelijkheden / Instala las dependencias de PHP
```bash
composer install
```

### 3. Installeer JavaScript-afhankelijkheden / Instala las dependencias de JavaScript
```bash
npm install
```

### 4. Configureer de omgeving / Configura el entorno
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configureer de database / Configura la base de datos
Wijzig de database-instellingen in het .env-bestand / Modifica la configuración de la base de datos en el archivo .env:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cei_bruselas
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Voer de migraties uit / Ejecuta las migraciones
```bash
php artisan migrate
```

### 7. Start de server / Inicia el servidor
```bash
php artisan serve
```

## 📁 Projectstructuur / Estructura del proyecto
```
CEI-BRUSELAS/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── StudentController.php
│   │   │   ├── ProfessorController.php
│   │   │   ├── SubjectController.php
│   │   │   └── ...
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Subject.php
│   │   └── ...
│   └── ...
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── students/
│   │   ├── professors/
│   │   ├── subjects/
│   │   └── ...
│   └── ...
└── ...
```

## 🔒 Beveiliging / Seguridad
- Gebruikersauthenticatie met Laravel Breeze / Autenticación de usuarios con Laravel Breeze
- Rolgebaseerde toegangscontrole / Control de acceso basado en roles
- CSRF-bescherming / Protección CSRF
- XSS-bescherming / Protección XSS

- Website: www.cei-bruselas.be



