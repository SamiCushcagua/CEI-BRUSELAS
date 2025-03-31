# E-commerce Project

Este es un proyecto de e-commerce desarrollado con Laravel que incluye funcionalidades de carrito de compras, gestión de productos, sistema de usuarios y más.

## Requisitos Previos

- PHP >= 8.1
- Composer
- MySQL
- Node.js & NPM

## Instalación

1. Clonar el repositorio:
```bash
git clone <URL_DEL_REPOSITORIO>
cd <NOMBRE_DEL_PROYECTO>
```

2. Instalar dependencias de PHP:
```bash
composer install
```

3. Instalar dependencias de Node.js:
```bash
npm install
```

4. Copiar el archivo de entorno:
```bash
cp .env.example .env
```

5. Generar la clave de la aplicación:
```bash
php artisan key:generate
```

6. Configurar la base de datos en el archivo .env:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

7. Ejecutar las migraciones y seeders:
```bash
php artisan migrate:fresh --seed
```

8. Compilar los assets:
```bash
npm run dev
```

9. Iniciar el servidor:
```bash
php artisan serve
```

## Credenciales por Defecto

### Usuario Administrador
- Email: admin@ehb.be
- Contraseña: Password!321

### Usuario Normal
- Email: user@example.com
- Contraseña: user123

## Características Implementadas

- Sistema de autenticación completo (login, registro, recuperación de contraseña)
- Gestión de productos (CRUD)
- Carrito de compras
- Panel de administración
- Sistema de usuarios con roles (admin/user)
- FAQ
- Foro de contacto
- Diseño responsive

## Tecnologías Utilizadas

- Laravel 10
- PHP 8.1
- MySQL
- Blade Templates
- Tailwind CSS
- Alpine.js

## Estructura del Proyecto

- `app/Http/Controllers/` - Controladores de la aplicación
- `app/Models/` - Modelos Eloquent
- `database/migrations/` - Migraciones de la base de datos
- `database/seeders/` - Seeders para datos de prueba
- `resources/views/` - Vistas Blade
- `routes/` - Definición de rutas

## Contribución

1. Fork el proyecto
2. Crear una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE.md](LICENSE.md) para más detalles.

## Contacto

Tu Nombre - [@tutwitter](https://twitter.com/tutwitter) - email@ejemplo.com

Link del Proyecto: [https://github.com/tuusuario/turepositorio](https://github.com/tuusuario/turepositorio)
