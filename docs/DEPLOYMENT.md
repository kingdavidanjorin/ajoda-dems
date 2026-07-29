# Ajoda DEMS Deployment Guide

## Requirements

- PHP 8.4 or newer
- MySQL 8 or newer
- Composer
- Node.js and npm
- HTTPS-enabled web server
- Queue worker
- Laravel scheduler
- Writable storage and cache directories

## Deployment commands

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan dems:system-check
```

## Queue worker

```bash
php artisan queue:work database --queue=notifications,default --sleep=3 --tries=3 --timeout=90
```

## Scheduler

Run every minute:

```bash
php artisan schedule:run
```

## Production environment

- APP_ENV=production
- APP_DEBUG=false
- QUEUE_CONNECTION=database
- HTTPS enabled
- Secure database and mail credentials
- Regular database backups
- Queue worker monitored
- Scheduler configured