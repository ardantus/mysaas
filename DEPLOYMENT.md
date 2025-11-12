# Deployment Guide

## Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Node.js 18+ and npm
- Web server (Apache/Nginx)

## Backend Deployment

### 1. Server Setup

```bash
# Clone repository
git clone https://github.com/ardantus/mysaas.git
cd mysaas/backend

# Install dependencies
composer install --optimize-autoloader --no-dev

# Copy environment file
cp .env.example .env
```

### 2. Environment Configuration

Edit `.env` file:

```env
APP_NAME=MySaaS
APP_ENV=production
APP_KEY=base64:GENERATED_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mysaas_central
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=redis
QUEUE_CONNECTION=database

# WhatsApp Configuration (optional)
WHATSAPP_API_URL=https://api.whatsapp.com/send
WHATSAPP_API_KEY=your_api_key
```

### 3. Application Setup

```bash
# Generate application key
php artisan key:generate

# Run migrations (central database)
php artisan migrate --force

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## Frontend Deployment

### 1. Build for Production

```bash
cd ../frontend

# Install dependencies
npm install

# Create environment file
cp .env.example .env
```

Edit `.env`:
```env
VITE_API_BASE_URL=https://api.yourdomain.com/api/v1
VITE_APP_NAME=MySaaS Store
VITE_APP_ENV=production
```

```bash
# Build for production
npm run build
```

### 2. Deploy Build Files

The built files will be in `frontend/dist/`. Deploy these to your web server or CDN.

## Web Server Configuration

### Apache Configuration

Create virtual host for main domain:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias *.yourdomain.com
    DocumentRoot /var/www/mysaas/backend/public

    <Directory /var/www/mysaas/backend/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/mysaas-error.log
    CustomLog ${APACHE_LOG_DIR}/mysaas-access.log combined
</VirtualHost>
```

Enable required modules:
```bash
a2enmod rewrite
a2enmod ssl
systemctl restart apache2
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com *.yourdomain.com;
    root /var/www/mysaas/backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## SSL Configuration

Use Let's Encrypt for free SSL:

```bash
# Install certbot
apt-get install certbot python3-certbot-apache

# Get certificate
certbot --apache -d yourdomain.com -d *.yourdomain.com
```

## Database Management

### Central Database

Contains:
- users
- stores
- migrations, cache, jobs tables

### Tenant Databases

Each store gets its own database:
- Automatically created when store is registered
- Contains: categories, products, carts, orders
- Named: `tenant_storename_timestamp`

## Queue Worker Setup

For background job processing:

```bash
# Create systemd service
nano /etc/systemd/system/mysaas-worker.service
```

```ini
[Unit]
Description=MySaaS Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/mysaas/backend/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
systemctl enable mysaas-worker
systemctl start mysaas-worker
```

## Scheduled Tasks

Add to crontab:

```bash
crontab -e
```

Add line:
```
* * * * * cd /var/www/mysaas/backend && php artisan schedule:run >> /dev/null 2>&1
```

## Monitoring

### Application Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View web server logs
tail -f /var/log/nginx/error.log  # Nginx
tail -f /var/log/apache2/error.log  # Apache
```

### Performance Monitoring

Consider using:
- Laravel Telescope (development)
- New Relic
- Sentry for error tracking

## Backup Strategy

### Database Backup

```bash
# Backup central database
mysqldump -u root -p mysaas_central > backup_central_$(date +%Y%m%d).sql

# Backup all tenant databases
mysql -u root -p -e "SHOW DATABASES LIKE 'tenant_%'" | grep -v Database | \
while read dbname; do
    mysqldump -u root -p $dbname > backup_${dbname}_$(date +%Y%m%d).sql
done
```

### Application Files Backup

```bash
# Backup storage directory
tar -czf storage_backup_$(date +%Y%m%d).tar.gz backend/storage/app/
```

## Security Considerations

1. **Environment Variables**: Never commit `.env` files
2. **File Permissions**: Proper permissions on storage and cache
3. **Database Credentials**: Use strong passwords
4. **SSL/TLS**: Always use HTTPS in production
5. **Regular Updates**: Keep Laravel and dependencies updated
6. **Rate Limiting**: Configure API rate limits
7. **CORS**: Configure allowed origins properly

## Scaling

### Horizontal Scaling

- Use load balancer for multiple app servers
- Shared storage (NFS/S3) for uploaded files
- Redis for session and cache
- Separate database server

### Vertical Scaling

- Increase server resources (CPU, RAM)
- Optimize database queries
- Use database indexes
- Enable OPcache for PHP

## Troubleshooting

### Common Issues

1. **Permission Errors**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

2. **Database Connection Failed**
   - Check database credentials in `.env`
   - Verify MySQL is running
   - Check firewall rules

3. **500 Internal Server Error**
   - Check Laravel logs: `storage/logs/laravel.log`
   - Enable debug mode temporarily: `APP_DEBUG=true`
   - Clear cache: `php artisan cache:clear`

4. **Subdomain Not Working**
   - Check DNS wildcard record
   - Verify web server configuration
   - Check TenantMiddleware is registered

## Support

For issues and questions:
- GitHub Issues: https://github.com/ardantus/mysaas/issues
- Documentation: See README.md
