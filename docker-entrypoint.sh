#!/bin/bash
set -e

echo "Starting User Service Initialization..."

# 1. Generate Passport Keys if they don't exist yet
# (This prevents overwriting keys every time the container restarts)
if [ ! -f /var/www/html/storage/oauth-private.key ]; then
    echo "Generating Laravel Passport encryption keys..."
    php artisan passport:keys --force
    
    # Ensure www-data owns the newly created keys so the web server can read them
    chown www-data:www-data /var/www/html/storage/oauth-*.key
    chmod 600 /var/www/html/storage/oauth-*.key
else
    echo "Passport keys already exist. Skipping generation."
fi

# 2. Inform the administrator about the Database requirement
echo "---------------------------------------------------------"
echo "NOTE: To create the Passport Password Client, you must run:"
echo "docker exec -it <container_name> php artisan passport:client --password"
echo "after the database is fully online and migrations have run."
echo "---------------------------------------------------------"

# 3. Hand off control back to the Docker CMD (starts php-fpm)
exec "$@"