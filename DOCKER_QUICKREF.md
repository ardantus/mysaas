# 🐳 Docker Quick Reference

## Essential Commands

```bash
# First time setup
make install

# Start services
make up          # or: docker-compose up -d

# Stop services
make down        # or: docker-compose down

# View logs
make logs        # or: docker-compose logs -f

# Access containers
make backend-shell    # or: docker-compose exec backend bash
make frontend-shell   # or: docker-compose exec frontend sh
make mysql-shell      # or: docker-compose exec mysql mysql -u mysaas_user -pmysaas_password

# Database operations
make migrate          # Run migrations
make seed            # Seed database
make backup-db       # Backup database

# Clear cache
make cache-clear     # Clear Laravel cache

# Laravel Tinker
make tinker         # or: docker-compose exec backend php artisan tinker
```

## Access Points

| Service | URL |
|---------|-----|
| Frontend | http://localhost:5173 |
| Backend API | http://localhost:8000 |
| PhpMyAdmin | http://localhost:8080 |
| MailHog | http://localhost:8025 |

## Common Tasks

### Create a Store
```bash
make tinker
```
```php
$user = User::first();
$store = app(App\Services\TenantService::class)->createStore([
    'user_id' => $user->id,
    'name' => 'My Store',
    'subdomain' => 'mystore',
    'whatsapp' => '628123456789',
]);
```

### View Logs
```bash
make logs-backend    # Backend logs
make logs-frontend   # Frontend logs
```

### Database Management
```bash
make mysql-shell     # Access MySQL
make backup-db       # Backup database
make migrate-fresh   # Fresh migration (WARNING: drops data)
```

### Troubleshooting
```bash
make ps              # Check service status
make restart         # Restart all services
make build           # Rebuild containers
make clean           # Remove everything (WARNING: data loss)
```

## Help
```bash
make help           # Show all available commands
```
