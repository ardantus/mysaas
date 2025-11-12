# Docker Setup Guide for MySaaS

## 🐳 Quick Start with Docker

Get the entire multi-tenant e-commerce platform running with a single command!

### Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/ardantus/mysaas.git
   cd mysaas
   ```

2. **Setup environment files**
   ```bash
   # Copy Docker environment files
   cp backend/.env.docker backend/.env
   cp frontend/.env.docker frontend/.env
   ```

3. **Start all services**
   ```bash
   docker-compose up -d
   ```

4. **Setup Laravel backend**
   ```bash
   # Generate application key
   docker-compose exec backend php artisan key:generate
   
   # Run migrations
   docker-compose exec backend php artisan migrate
   
   # (Optional) Seed database
   docker-compose exec backend php artisan db:seed
   ```

5. **Access the application**
   - **Frontend**: http://localhost:5173
   - **Backend API**: http://localhost:8000
   - **Nginx (Alternative)**: http://localhost
   - **PhpMyAdmin**: http://localhost:8080
   - **MailHog**: http://localhost:8025

## 📦 Services Included

| Service | Port | Description |
|---------|------|-------------|
| **MySQL** | 3306 | Central database + tenant databases |
| **Redis** | 6379 | Caching and session storage |
| **Backend** | 8000 | Laravel API server |
| **Frontend** | 5173 | Vue.js development server |
| **Nginx** | 80, 443 | Web server (optional) |
| **PhpMyAdmin** | 8080 | Database management UI |
| **MailHog** | 1025, 8025 | Email testing (SMTP + Web UI) |

## 🚀 Docker Commands

### Basic Operations

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f backend
docker-compose logs -f frontend

# Restart a service
docker-compose restart backend

# Rebuild containers
docker-compose up -d --build
```

### Backend Commands

```bash
# Access Laravel container
docker-compose exec backend bash

# Run Artisan commands
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear

# Run tests
docker-compose exec backend php artisan test

# Install Composer dependencies
docker-compose exec backend composer install

# Create a new store (via Tinker)
docker-compose exec backend php artisan tinker
```

### Frontend Commands

```bash
# Access frontend container
docker-compose exec frontend sh

# Install npm dependencies
docker-compose exec frontend npm install

# Run build
docker-compose exec frontend npm run build

# Run tests
docker-compose exec frontend npm run test
```

### Database Commands

```bash
# Access MySQL
docker-compose exec mysql mysql -u mysaas_user -pmysaas_password mysaas_central

# Backup database
docker-compose exec mysql mysqldump -u mysaas_user -pmysaas_password mysaas_central > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u mysaas_user -pmysaas_password mysaas_central < backup.sql

# List all databases (including tenant databases)
docker-compose exec mysql mysql -u root -proot_password -e "SHOW DATABASES;"
```

### Redis Commands

```bash
# Access Redis CLI
docker-compose exec redis redis-cli

# Clear all cache
docker-compose exec redis redis-cli FLUSHALL

# Monitor Redis
docker-compose exec redis redis-cli MONITOR
```

## 🏗️ Project Structure with Docker

```
mysaas/
├── docker-compose.yml          # Main Docker Compose configuration
├── docker/
│   ├── backend/
│   │   └── Dockerfile         # Laravel backend container
│   ├── frontend/
│   │   └── Dockerfile         # Vue.js frontend container
│   ├── nginx/
│   │   └── default.conf       # Nginx configuration
│   └── mysql/
│       └── init/
│           └── 01-init.sql    # MySQL initialization
├── backend/
│   ├── .env.docker            # Docker environment template
│   └── ...
└── frontend/
    ├── .env.docker            # Docker environment template
    └── ...
```

## 🔧 Configuration

### Database Configuration

The MySQL container is configured with:
- **Root Password**: `root_password`
- **Database**: `mysaas_central`
- **User**: `mysaas_user`
- **Password**: `mysaas_password`

To change these, edit `docker-compose.yml` and `backend/.env`.

### PHP Configuration

To customize PHP settings, create `docker/backend/php.ini`:

```ini
upload_max_filesize = 50M
post_max_size = 50M
memory_limit = 256M
max_execution_time = 300
```

Then mount it in `docker-compose.yml`:
```yaml
volumes:
  - ./docker/backend/php.ini:/usr/local/etc/php/conf.d/custom.ini
```

### Network Configuration

All services run in a bridged network called `mysaas_network`. Services can communicate using their service names:
- Backend → `http://backend:8000`
- MySQL → `mysql:3306`
- Redis → `redis:6379`

## 🎯 Development Workflow

### 1. Making Code Changes

Changes to code are automatically reflected:
- **Backend**: Volumes mounted, changes appear immediately
- **Frontend**: Vite HMR updates automatically

### 2. Database Migrations

After creating new migrations:
```bash
docker-compose exec backend php artisan migrate
```

### 3. Creating a New Store

```bash
docker-compose exec backend php artisan tinker
```

```php
use App\Services\TenantService;
use App\Models\User;

$user = User::first(); // or create one
$tenantService = app(TenantService::class);

$store = $tenantService->createStore([
    'user_id' => $user->id,
    'name' => 'My Docker Store',
    'subdomain' => 'dockerstore',
    'whatsapp' => '628123456789',
]);
```

### 4. Testing WhatsApp Integration

WhatsApp messages are logged. View them:
```bash
docker-compose logs -f backend | grep WhatsApp
```

## 📧 Email Testing with MailHog

All emails are captured by MailHog:
1. Open http://localhost:8025
2. Trigger an email from the application
3. View it in MailHog UI

## 🗄️ Database Management

### Using PhpMyAdmin

1. Open http://localhost:8080
2. Login:
   - **Server**: `mysql`
   - **Username**: `root`
   - **Password**: `root_password`

### Viewing Tenant Databases

Tenant databases are automatically created with pattern `tenant_*`:
```bash
docker-compose exec mysql mysql -u root -proot_password -e "SHOW DATABASES LIKE 'tenant_%';"
```

## 🐛 Troubleshooting

### Port Already in Use

If a port is already in use, edit `docker-compose.yml`:
```yaml
ports:
  - "8001:8000"  # Change 8000 to 8001
```

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec backend chmod -R 775 storage bootstrap/cache
docker-compose exec backend chown -R www-data:www-data storage bootstrap/cache
```

### Clear All Data and Restart

```bash
# Stop and remove all containers, volumes
docker-compose down -v

# Rebuild and start fresh
docker-compose up -d --build
```

### Container Won't Start

Check logs for errors:
```bash
docker-compose logs backend
docker-compose logs mysql
```

### Database Connection Failed

1. Ensure MySQL is healthy:
   ```bash
   docker-compose ps
   ```

2. Check database credentials in `backend/.env`

3. Restart MySQL:
   ```bash
   docker-compose restart mysql
   ```

## 🔒 Security Notes

⚠️ **Important**: This Docker setup is for **development only**!

For production:
- Change all default passwords
- Use environment-specific secrets
- Enable SSL/TLS
- Configure firewall rules
- Use Docker secrets for sensitive data
- Review and harden configurations

## 📊 Resource Usage

Typical resource consumption:
- **Memory**: ~2GB RAM
- **Disk**: ~5GB (with volumes)
- **CPU**: Minimal (< 10% on modern systems)

To limit resources, add to `docker-compose.yml`:
```yaml
services:
  backend:
    deploy:
      resources:
        limits:
          cpus: '0.5'
          memory: 512M
```

## 🚀 Production Deployment

For production, consider:
1. Use Docker Swarm or Kubernetes
2. Implement proper secrets management
3. Use production-grade databases (managed services)
4. Configure load balancers
5. Set up monitoring and logging
6. Implement backup strategies

See [DEPLOYMENT.md](../DEPLOYMENT.md) for detailed production deployment guide.

## 🆘 Getting Help

- Check logs: `docker-compose logs -f`
- Verify services: `docker-compose ps`
- Check networks: `docker network ls`
- Inspect containers: `docker inspect mysaas_backend`

## 📚 Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Docker Best Practices](https://laravel.com/docs/sail)
- [Vue.js Docker Deployment](https://vuejs.org/guide/best-practices/production-deployment.html)

---

**Happy Docker Development!** 🐳🚀
