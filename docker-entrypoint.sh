#!/bin/bash
set -e

echo "🚀 Iniciando aplicación Laravel..."

# Verificar si existe el archivo .env
if [ ! -f .env ]; then
    echo "⚠️  Archivo .env no encontrado, copiando desde .env.example"
    cp .env.example .env
fi

# Generar key si no existe
if ! grep -q "APP_KEY=" .env || [ -z "$(grep APP_KEY= .env | cut -d'=' -f2)" ]; then
    echo "🔑 Generando APP_KEY..."
    php artisan key:generate --force
fi

# Función para esperar a que la base de datos esté lista
wait_for_db() {
    echo "🔌 Esperando a que PostgreSQL esté disponible..."
    
    while ! php artisan migrate:status > /dev/null 2>&1; do
        echo "⏳ Esperando conexión a la base de datos..."
        sleep 2
    done
    
    echo "✅ Base de datos conectada!"
}

# Esperar a que la base de datos esté lista
wait_for_db

# Ejecutar migraciones
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force

# Limpiar cachés
echo "🧹 Limpiando cachés..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generar cachés optimizados
echo "⚡ Generando cachés optimizados..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generar documentación de API
echo "📚 Generando documentación de API..."
php artisan l5-swagger:generate

# Optimizar para producción
echo "🚀 Optimizando para producción..."
php artisan optimize

echo "✅ Aplicación lista!"

# Ejecutar Apache
exec apache2-foreground