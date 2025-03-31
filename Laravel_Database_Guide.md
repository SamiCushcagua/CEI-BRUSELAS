# Guía Detallada: Creación y Gestión de Base de Datos en Laravel

## 0. Conceptos Básicos

### ¿Qué es un Modelo?
Un Modelo en Laravel es una clase que representa una tabla de la base de datos. Es parte del patrón MVC (Modelo-Vista-Controlador) y:
- Cada modelo se corresponde con una tabla específica
- Maneja la lógica de datos y las reglas de negocio
- Permite interactuar con la base de datos de forma orientada a objetos
- Se encuentra en la carpeta `app/Models`

Ejemplo:
```php
// El modelo Product representa la tabla 'products'
class Product extends Model
{
    // Definimos qué campos pueden ser llenados masivamente
    protected $fillable = ['name', 'description'];
}
```

### ¿Qué es Eloquent?
Eloquent es el ORM (Object-Relational Mapping) de Laravel que:
- Convierte las tablas en clases PHP (Modelos)
- Convierte los registros en objetos
- Permite escribir consultas usando PHP en lugar de SQL
- Maneja las relaciones entre tablas (uno a uno, uno a muchos, etc.)

Ejemplos de uso:
```php
// SQL: SELECT * FROM products;
$products = Product::all();

// SQL: SELECT * FROM products WHERE price > 100;
$products = Product::where('price', '>', 100)->get();

// SQL: INSERT INTO products (name, price) VALUES ('Laptop', 999);
Product::create(['name' => 'Laptop', 'price' => 999]);
```

### ¿Qué es una Migración?
Una migración es como un "control de versiones" para tu base de datos que:
- Define la estructura de las tablas usando código PHP
- Permite crear/modificar/eliminar tablas y columnas
- Hace que los cambios en la base de datos sean reproducibles
- Se encuentra en la carpeta `database/migrations`

Ejemplo:
```php
// Esta migración crea una tabla 'products'
public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();                 // Columna ID autoincremental
        $table->string('name');       // Columna VARCHAR para el nombre
        $table->text('description');  // Columna TEXT para la descripción
        $table->timestamps();         // Columnas created_at y updated_at
    });
}
```

### ¿Qué es un Seeder?
Un Seeder es una clase que:
- Se usa para poblar la base de datos con datos de prueba
- Permite tener datos iniciales en la aplicación
- Es útil para desarrollo y pruebas
- Se encuentra en la carpeta `database/seeders`

Ejemplo:
```php
class ProductSeeder extends Seeder
{
    public function run()
    {
        // Crea productos de prueba
        Product::create([
            'name' => 'Producto 1',
            'description' => 'Descripción 1'
        ]);
    }
}
```

### ¿Qué es una Factory?
Una Factory es una clase que:
- Genera datos falsos pero realistas para pruebas
- Usa la librería Faker para generar datos aleatorios
- Permite crear grandes cantidades de datos de prueba
- Se encuentra en la carpeta `database/factories`

Ejemplo:
```php
class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'price' => $this->faker->randomFloat(2, 10, 1000)
        ];
    }
}
```

### Ciclo de Vida de los Datos
1. **Migración**: Define la estructura de la tabla
2. **Modelo**: Define cómo interactuar con la tabla
3. **Factory**: Define cómo generar datos falsos (opcional)
4. **Seeder**: Inserta datos iniciales o de prueba
5. **Eloquent**: Se usa para realizar operaciones CRUD (Create, Read, Update, Delete)

## 0. Convenciones de Nombres en Laravel

### Nombres de Modelos
- **Singular y PascalCase**: `Product`, `User`, `OrderDetail`
- **Singular porque representa una instancia**: Un modelo representa un registro individual
- **Ejemplos**:
  - ✅ `Product` → tabla `products`
  - ✅ `OrderItem` → tabla `order_items`
  - ✅ `Category` → tabla `categories`
  - ❌ `Products` (no usar plural)
  - ❌ `product` (no usar minúsculas)
  - ❌ `order_item` (no usar snake_case)

### Nombres de Tablas
- **Plural y snake_case**: `products`, `users`, `order_details`
- **Plural porque contiene múltiples registros**
- **Ejemplos**:
  - ✅ `products` ← modelo `Product`
  - ✅ `order_items` ← modelo `OrderItem`
  - ✅ `categories` ← modelo `Category`
  - ❌ `product` (no usar singular)
  - ❌ `orderItems` (no usar camelCase)
  - ❌ `OrderDetails` (no usar PascalCase)

### Nombres de Migraciones
- **Formato**: `YYYY_MM_DD_HHMMSS_create_table_name_table.php`
- **Usar verbos descriptivos**: create, add, remove, update
- **Ejemplos**:
  - ✅ `2024_03_15_create_products_table.php`
  - ✅ `2024_03_15_add_price_to_products_table.php`
  - ✅ `2024_03_15_update_products_table.php`
  - ❌ `products.php` (falta el formato timestamp)
  - ❌ `create_products.php` (falta _table al final)

### Nombres de Seeders
- **Sufijo Seeder y PascalCase**: `ProductSeeder`, `UserSeeder`
- **Ejemplos**:
  - ✅ `ProductSeeder`
  - ✅ `OrderItemSeeder`
  - ✅ `CategorySeeder`
  - ❌ `Products` (falta el sufijo Seeder)
  - ❌ `productSeeder` (no usar camelCase)
  - ❌ `Product_Seeder` (no usar snake_case)

### Nombres de Factories
- **Sufijo Factory y PascalCase**: `ProductFactory`, `UserFactory`
- **Ejemplos**:
  - ✅ `ProductFactory`
  - ✅ `OrderItemFactory`
  - ✅ `CategoryFactory`
  - ❌ `Products` (falta el sufijo Factory)
  - ❌ `productFactory` (no usar camelCase)
  - ❌ `Product_Factory` (no usar snake_case)

### Nombres de Columnas
- **snake_case para nombres de columnas**
- **Ejemplos**:
  - ✅ `first_name`
  - ✅ `created_at`
  - ✅ `product_id`
  - ❌ `firstName` (no usar camelCase)
  - ❌ `FirstName` (no usar PascalCase)
  - ❌ `productid` (no usar todo minúsculas juntas)

### Claves Foráneas
- **Formato**: `tabla_singular_id`
- **Ejemplos**:
  - ✅ `product_id`
  - ✅ `user_id`
  - ✅ `category_id`
  - ❌ `productid` (falta el guion bajo)
  - ❌ `products_id` (no usar plural)
  - ❌ `id_product` (orden incorrecto)

### Relaciones en Modelos
- **hasOne/belongsTo**: nombre en singular
- **hasMany/belongsToMany**: nombre en plural
- **Ejemplos**:
```php
// En el modelo User
public function profile() // singular para uno a uno
{
    return $this->hasOne(Profile::class);
}

public function orders() // plural para uno a muchos
{
    return $this->hasMany(Order::class);
}

public function roles() // plural para muchos a muchos
{
    return $this->belongsToMany(Role::class);
}
```

## 1. Configuración Inicial

### 1.1 Configurar el archivo .env
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 1.2 Crear la base de datos
```bash
# Acceder a MySQL
mysql -u root -p

# Crear la base de datos
CREATE DATABASE nombre_de_tu_base_de_datos;
```

## 2. Crear el Modelo y la Migración

### 2.1 Crear el modelo con su migración
```bash
php artisan make:model Product -m
```
Este comando crea:
- `app/Models/Product.php`
- `database/migrations/YYYY_MM_DD_HHMMSS_create_products_table.php`

### 2.2 Configurar la migración
Archivo: `database/migrations/YYYY_MM_DD_HHMMSS_create_products_table.php`
```php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description');
        $table->timestamps();
    });
}
```

### 2.3 Configurar el modelo
Archivo: `app/Models/Product.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];
}
```

## 3. Crear el Seeder

### 3.1 Crear el seeder
```bash
php artisan make:seeder ProductSeeder
```

### 3.2 Configurar el seeder
Archivo: `database/seeders/ProductSeeder.php`
```php
<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'Product 1',
            'description' => 'Description 1',
        ]);
        Product::create([
            'name' => 'Product 2',
            'description' => 'Description 2',
        ]);
    }
}
```

### 3.3 Registrar el seeder
Archivo: `database/seeders/DatabaseSeeder.php`
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProductSeeder::class);
    }
}
```

## 4. Ejecutar Migraciones y Seeders

```bash
# Ejecutar migraciones y seeders
php artisan migrate:fresh --seed
```

## 5. Crear la Ruta

Archivo: `routes/web.php`
```php
<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $products = Product::find(1);  // Obtiene el primer producto
    return view('welcome', ['products' => $products]);
});
```

## 6. Crear la Vista

Archivo: `resources/views/welcome.blade.php`
```php
@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h1 class="text-2xl font-bold mb-4">Product Information</h1>
                
                <div class="mt-6">
                    <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
                        <p><strong>Name:</strong> {{ $products->name }}</p>
                        <p><strong>Description:</strong> {{ $products->description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

## 7. Comandos Útiles para Gestionar la Base de Datos

```bash
# Ver el estado de las migraciones
php artisan migrate:status

# Revertir todas las migraciones
php artisan migrate:rollback

# Revertir y volver a ejecutar migraciones
php artisan migrate:refresh

# Eliminar todas las tablas y volver a migrar
php artisan migrate:fresh

# Ejecutar solo los seeders
php artisan db:seed
```

## 8. Consultas Eloquent Útiles

```php
// Obtener todos los productos
$products = Product::all();

// Obtener el primer producto
$product = Product::first();

// Obtener un producto por ID
$product = Product::find(1);

// Obtener productos con condiciones
$products = Product::where('name', 'Product 1')->get();

// Crear un nuevo producto
Product::create([
    'name' => 'Nuevo Producto',
    'description' => 'Nueva Descripción'
]);

// Actualizar un producto
$product = Product::find(1);
$product->update([
    'name' => 'Nombre Actualizado'
]);

// Eliminar un producto
$product = Product::find(1);
$product->delete();
```

## 9. Verificar los Datos en la Base de Datos

### 9.1 Usando Tinker
```bash
# Abrir Tinker
php artisan tinker

# Consultar productos
>>> Product::all();
>>> Product::first();
```

### 9.2 Usando MySQL directamente
```bash
# Acceder a MySQL
mysql -u root -p

# Seleccionar la base de datos
use nombre_de_tu_base_de_datos;

# Ver los productos
SELECT * FROM products;
```

## 10. Solución de Problemas Comunes

1. **Error: Table not found**
   - Verificar que las migraciones se ejecutaron correctamente
   - Verificar el nombre de la tabla en la migración
   - Ejecutar `php artisan migrate:status`

2. **Error: Column not found**
   - Verificar que la columna existe en la migración
   - Verificar que el campo está en `$fillable` del modelo
   - Ejecutar `php artisan migrate:fresh`

3. **Error: Class not found**
   - Verificar los namespaces
   - Ejecutar `composer dump-autoload`

4. **Error: Data not showing in view**
   - Verificar que los datos existen en la base de datos
   - Verificar que la variable está siendo pasada a la vista
   - Verificar la sintaxis de Blade en la vista 