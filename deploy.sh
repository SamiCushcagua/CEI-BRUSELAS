#!/bin/bash
# Script para actualizar el proyecto en el servidor (DigitalOcean/Linux)
# Uso: ./deploy.sh   (ejecutar desde la raíz del proyecto en el servidor)

set -e

echo "🔧 Activando modo mantenimiento..."
php artisan down

echo "🔄 Actualizando desde GitHub..."
git pull origin main

echo "📦 Instalando dependencias PHP..."
composer install --no-dev --optimize-autoloader

echo "🗃️ Migraciones (si hay nuevas)..."
php artisan migrate --force

echo "🧹 Limpiando y regenerando cachés..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🌐 Desactivando modo mantenimiento..."
php artisan up

echo "✅ Despliegue completado."
