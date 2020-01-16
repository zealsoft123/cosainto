ssh root@157.230.58.171 << EOF
cd /var/www/cosainto
git fetch
git add .
git stash
git checkout master
git pull origin master
composer update
composer install
php artisan migrate