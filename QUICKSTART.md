# Quick Start Guide

Get your multi-tenant e-commerce SaaS up and running in minutes!

## 🚀 Quick Installation

### Prerequisites Check

```bash
# Check PHP version (needs 8.2+)
php -v

# Check Composer
composer --version

# Check Node.js (needs 18+)
node --version
npm --version

# Check MySQL
mysql --version
```

### Step 1: Clone and Setup Backend

```bash
# Clone repository
git clone https://github.com/ardantus/mysaas.git
cd mysaas/backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create database
mysql -u root -p -e "CREATE DATABASE mysaas_central"

# Configure .env file
nano .env
```

Update these values in `.env`:
```env
DB_DATABASE=mysaas_central
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

```bash
# Run migrations
php artisan migrate

# Start server
php artisan serve
```

Backend is now running at `http://localhost:8000`

### Step 2: Setup Frontend

```bash
# Open new terminal
cd ../frontend

# Install dependencies
npm install

# Create environment file
cp .env.example .env

# Start development server
npm run dev
```

Frontend is now running at `http://localhost:5173`

## 🎯 First Steps

### 1. Register a User

Use Laravel Sanctum or create a user via tinker:

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('password');
$user->save();
```

### 2. Create Your First Store

Using API or create via tinker:

```php
use App\Models\Store;
use App\Services\TenantService;

$tenantService = app(TenantService::class);

$store = $tenantService->createStore([
    'user_id' => 1,
    'name' => 'My First Store',
    'subdomain' => 'myfirststore',
    'whatsapp' => '628123456789',
    'description' => 'My awesome store',
    'address' => 'Jakarta, Indonesia'
]);
```

This automatically creates a tenant database for the store!

### 3. Add Categories and Products

Access the store via subdomain (configure your hosts file for testing):

```bash
# Add to /etc/hosts (Linux/Mac) or C:\Windows\System32\drivers\etc\hosts (Windows)
127.0.0.1 myfirststore.localhost
```

Then create categories and products via API or tinker:

```php
// Switch to tenant database
$tenantService->switchToTenant($store);

// Create category
$category = \App\Models\Category::create([
    'name' => 'Electronics',
    'slug' => 'electronics',
    'is_active' => true,
]);

// Create product
$product = \App\Models\Product::create([
    'category_id' => $category->id,
    'name' => 'Smartphone X',
    'slug' => 'smartphone-x',
    'description' => 'Latest smartphone',
    'price' => 5000000,
    'stock' => 10,
    'is_active' => true,
]);
```

### 4. Test the Store

Visit `http://myfirststore.localhost:8000` (or your configured subdomain) to see the store frontend!

## 🛠️ Development Workflow

### Running Tests

```bash
cd backend
php artisan test
```

### Code Style

```bash
cd backend
./vendor/bin/pint
```

### Frontend Development

```bash
cd frontend
npm run dev    # Development server
npm run build  # Production build
npm run test   # Run tests
```

## 📱 Testing WhatsApp Integration

WhatsApp integration is logged by default. Check logs:

```bash
tail -f backend/storage/logs/laravel.log
```

To integrate with real WhatsApp API:
1. Sign up for WhatsApp Business API (Twilio, Vonage, etc.)
2. Update `WhatsAppService.php` with your API credentials
3. Implement actual API calls in the `sendMessage` method

## 🔧 Troubleshooting

### Common Issues

**Database connection failed:**
```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u root -p
```

**Permission denied on storage:**
```bash
chmod -R 775 backend/storage
chmod -R 775 backend/bootstrap/cache
```

**Port already in use:**
```bash
# Use different port
php artisan serve --port=8001
```

**Frontend can't connect to API:**
- Check `VITE_API_BASE_URL` in `frontend/.env`
- Enable CORS in backend if needed

## 📚 Next Steps

1. **Authentication**: Implement Laravel Sanctum authentication
2. **File Uploads**: Add image upload for products
3. **Payment**: Integrate payment gateway (Midtrans, Xendit)
4. **Email**: Setup email notifications
5. **Customization**: Customize store themes
6. **Domain**: Configure custom domains for stores

## 🤝 Need Help?

- Read the full [README.md](README.md)
- Check [API Documentation](API.md)
- See [Deployment Guide](DEPLOYMENT.md)
- Open an issue on GitHub

## 🎉 Success!

Your multi-tenant e-commerce platform is ready! Each store registered will:
- Get its own subdomain (e.g., `storename.yourdomain.com`)
- Have a separate database
- Send WhatsApp notifications automatically
- Manage products, orders, and customers independently

Happy coding! 🚀
