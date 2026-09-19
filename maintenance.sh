composer install
php artisan migrate --seed
graphify . --code-only --exclude "vendor/*,node_modules/*,storage/*,public/*,bootstrap/cache/*"
graphify cluster-only .
