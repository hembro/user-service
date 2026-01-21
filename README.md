## Configure Laravel Passport
run the following:
php artisan passport:keys
# This will generate public and private keys
# Public keys should be shared in every microservices.

php artisan passport:client --password (Required for access and refresh token generation)
