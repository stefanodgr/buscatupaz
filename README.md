## Baselang Production

## Requirements

- Mysql/MariaDB > 5
- PHP > 7.1


## Documentation

Laravel 5.5 Docs:  https://laravel.com/docs/5.5/

## Staging and Local Development

After updating or checkout of code you can execute artisan migrate to execute possible database updates.

php artisan migrate

We have three different environments:

- local 
- staging
- production

(every environment have a file .env )

php -d memory_limit=-1 artisan migrate

# How to create a file to execute a database modification:

php -d memory_limit=-1 artisan make:migration migration_name

# Production Deployment

After copy in production the cache should be updated with the commands.

php artisan cache:clear

# Cron Jobs

wget "http://staging.baselang.com/cron/check-referral" >/dev/null 2>&1

wget "http://staging.baselang.com/cron/send-link" >/dev/null 2>&1