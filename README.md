# mysaas - Multi-Tenant E-commerce SaaS

A comprehensive multi-tenant online store platform built with Laravel backend and Vue.js frontend.

## Features

### Multi-Tenancy
- Each store has its own subdomain/domain
- Separate database for each store
- Central database for store management
- Automatic tenant database creation and migration

### Store Management
- Store registration and setup
- Store owner authentication
- Store settings and configuration
- WhatsApp integration for notifications

### E-commerce Features
- **Category Management**: Create and organize product categories with hierarchy
- **Product Management**: Add, edit, and manage products with:
  - Images and specifications
  - Stock management
  - Pricing (with compare price)
  - SKU tracking
  - Active/featured status
- **Shopping Cart**: Session-based cart management
- **Order System**: Complete order processing with status tracking

### WhatsApp Integration
- Automatic checkout notification to store owner
- Order status updates sent to customers
- Indonesian phone number format support

## Architecture

### Backend (Laravel 11)
- **Models**: Store, Category, Product, Cart, CartItem, Order, OrderItem
- **Services**: 
  - `TenantService`: Multi-tenant database management
  - `WhatsAppService`: WhatsApp notification integration
- **Middleware**: `TenantMiddleware` for automatic tenant switching
- **API Routes**: RESTful API for all operations

### Frontend (Vue.js 3)
- TypeScript support
- Pinia for state management
- Vue Router for navigation
- Component-based architecture

## Database Structure

### Central Database (Main)
- `users`: User authentication
- `stores`: Store information and configuration
- Standard Laravel tables (migrations, cache, jobs)

### Tenant Databases (Per Store)
- `categories`: Product categories
- `products`: Store products
- `carts`: Shopping carts
- `cart_items`: Cart line items
- `orders`: Customer orders
- `order_items`: Order line items

## Setup Instructions

### 🐳 Quick Start with Docker (Recommended)

The easiest way to get started! Docker handles all dependencies automatically.

```bash
# Clone and setup
git clone https://github.com/ardantus/mysaas.git
cd mysaas

# Start with Docker
make install

# Or manually:
docker-compose up -d
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate
```

Access the application:
- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000
- **PhpMyAdmin**: http://localhost:8080
- **MailHog**: http://localhost:8025

📚 **Full Docker documentation**: See [DOCKER.md](DOCKER.md)

### Manual Setup (Alternative)

If you prefer manual installation without Docker:

#### Backend Setup

1. Install dependencies:
```bash
cd backend
composer install
```

2. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Update `.env` with database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mysaas_central
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. Run migrations:
```bash
php artisan migrate
```

5. Start the server:
```bash
php artisan serve
```

### Frontend Setup

1. Install dependencies:
```bash
cd frontend
npm install
```

2. Configure API endpoint in `src/config.ts`

3. Start development server:
```bash
npm run dev
```

## API Endpoints

### Central API (Main Domain)
- `POST /api/v1/stores` - Create new store (authenticated)
- `GET /api/v1/stores` - List user's stores (authenticated)
- `PUT /api/v1/stores/{id}` - Update store (authenticated)
- `DELETE /api/v1/stores/{id}` - Delete store (authenticated)

### Tenant API (Store Subdomains)

#### Public Endpoints
- `GET /api/v1/categories` - List categories
- `GET /api/v1/products` - List products
- `GET /api/v1/products/{id}` - Get product details
- `POST /api/v1/cart/add` - Add item to cart
- `PUT /api/v1/cart/update/{item}` - Update cart item
- `DELETE /api/v1/cart/remove/{item}` - Remove cart item
- `POST /api/v1/orders/checkout` - Create order

#### Admin Endpoints (Authenticated)
- `POST /api/v1/admin/categories` - Create category
- `PUT /api/v1/admin/categories/{id}` - Update category
- `DELETE /api/v1/admin/categories/{id}` - Delete category
- `POST /api/v1/admin/products` - Create product
- `PUT /api/v1/admin/products/{id}` - Update product
- `DELETE /api/v1/admin/products/{id}` - Delete product
- `GET /api/v1/admin/orders` - List orders
- `PUT /api/v1/admin/orders/{id}/status` - Update order status

## License

This project is open-sourced software licensed under the Apache 2.0 license.

