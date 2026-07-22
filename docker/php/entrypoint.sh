php artisan migrate --force

php artisan db:seed --class=RoleSeeder --force

php artisan db:seed --class=SuperAdminSeeder --force

php artisan storage:link || true

php artisan optimize:clear

php artisan optimize