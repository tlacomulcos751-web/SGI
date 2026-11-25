#!/bin/bash
set -e

echo "=========================================================="
echo "    SGI - Sistema de Gestión de Inventario (Producción)   "
echo "=========================================================="

# 1. Asegurar directorios de storage y bootstrap/cache con permisos adecuados
echo "===> [1/5] Verificando permisos en storage y bootstrap/cache..."
rm -f /var/www/html/bootstrap/cache/*.php /var/www/html/bootstrap/cache/*.tmp

mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Regenerar catálogo de paquetes descubiertos limpio para producción
php artisan package:discover --ansi || true

# 2. Asegurar enlace simbólico de public/storage
echo "===> [2/5] Verificando storage link..."
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link --force || true
fi

# 3. Esperar a la base de datos si DB_HOST está configurado
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ] && [ "$DB_HOST" != "localhost" ]; then
    echo "===> [3/5] Comprobando disponibilidad de MySQL ($DB_HOST:${DB_PORT:-3306})..."
    max_tries=30
    count=0
    until php -r "
        \$host = getenv('DB_HOST');
        \$port = (int)(getenv('DB_PORT') ?: 3306);
        \$fp = @fsockopen(\$host, \$port, \$errno, \$errstr, 2);
        if (\$fp) { fclose(\$fp); exit(0); } else { exit(1); }
    " 2>/dev/null; do
        count=$((count+1))
        if [ $count -ge $max_tries ]; then
            echo "AVISO: No se pudo verificar puerto de base de datos tras $max_tries intentos. Continuando inicio..."
            break
        fi
        sleep 1
    done
    echo "===> Base de datos alcanzable."
else
    echo "===> [3/5] DB_HOST es local o no está definido, omitiendo sondeo de puerto."
fi

# 4. Migraciones controladas
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "===> [4/5] Ejecutando migraciones pendientes (modo seguro)..."
    php artisan migrate --force --isolated || echo "AVISO: Migración no pudo ejecutarse o no hubo cambios."
else
    echo "===> [4/5] RUN_MIGRATIONS=false. Omitiendo migraciones automáticas."
fi

# 5. Optimización de Laravel
echo "===> [5/5] Preparando cachés de Laravel..."
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
    
    if [ "${SKIP_OPTIMIZE:-false}" != "true" ]; then
        php artisan config:cache || true
        php artisan route:cache || true
        php artisan view:cache || true
        echo "===> Cachés de configuración, rutas y vistas optimizadas."
    fi
else
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
    echo "===> Caché en modo desarrollo/limpio."
fi

echo "===> SGI listo para recibir peticiones en el puerto ${PORT:-80}."
exec "$@"
