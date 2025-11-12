# API Documentation

## Base URLs

- **Central API**: `https://yourdomain.com/api/v1`
- **Tenant API**: `https://{subdomain}.yourdomain.com/api/v1` or `https://{custom-domain}/api/v1`

## Authentication

Most admin endpoints require authentication using Laravel Sanctum:

```http
Authorization: Bearer {token}
```

## Response Format

### Success Response
```json
{
  "data": {},
  "message": "Success message"
}
```

### Error Response
```json
{
  "error": "Error message",
  "message": "Detailed error description"
}
```

### Paginated Response
```json
{
  "data": [],
  "current_page": 1,
  "per_page": 20,
  "total": 100,
  "last_page": 5
}
```

---

## Central API Endpoints

### Store Management

#### List Stores
```http
GET /api/v1/stores
```

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "My Store",
      "slug": "my-store",
      "subdomain": "mystore",
      "domain": "mystore.com",
      "whatsapp": "628123456789",
      "is_active": true,
      "created_at": "2025-11-12T10:00:00Z"
    }
  ]
}
```

#### Create Store
```http
POST /api/v1/stores
```

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Body:**
```json
{
  "name": "My Store",
  "subdomain": "mystore",
  "domain": "mystore.com",
  "whatsapp": "628123456789",
  "phone": "021-1234567",
  "description": "Store description",
  "address": "Store address"
}
```

**Response:**
```json
{
  "store": {
    "id": 1,
    "name": "My Store",
    "slug": "my-store",
    "subdomain": "mystore",
    "database_name": "tenant_my_store_1234567890",
    "whatsapp": "628123456789"
  },
  "message": "Store created successfully"
}
```

#### Get Store
```http
GET /api/v1/stores/{id}
```

#### Update Store
```http
PUT /api/v1/stores/{id}
```

**Body:**
```json
{
  "name": "Updated Store Name",
  "whatsapp": "628123456789",
  "description": "Updated description"
}
```

#### Delete Store
```http
DELETE /api/v1/stores/{id}
```

---

## Tenant API Endpoints

### Categories

#### List Categories
```http
GET /api/v1/categories
```

**Query Parameters:**
- `parent_id` (optional): Filter by parent category

**Response:**
```json
[
  {
    "id": 1,
    "name": "Electronics",
    "slug": "electronics",
    "description": "Electronic products",
    "image": "https://example.com/image.jpg",
    "order": 0,
    "is_active": true,
    "children": []
  }
]
```

#### Get Category
```http
GET /api/v1/categories/{id}
```

**Response:**
```json
{
  "id": 1,
  "name": "Electronics",
  "slug": "electronics",
  "products": [
    {
      "id": 1,
      "name": "Product 1",
      "price": 100000
    }
  ],
  "children": []
}
```

#### Create Category (Admin)
```http
POST /api/v1/admin/categories
```

**Headers:**
- `Authorization: Bearer {token}`

**Body:**
```json
{
  "name": "Electronics",
  "description": "Electronic products",
  "parent_id": null,
  "order": 0,
  "is_active": true
}
```

#### Update Category (Admin)
```http
PUT /api/v1/admin/categories/{id}
```

#### Delete Category (Admin)
```http
DELETE /api/v1/admin/categories/{id}
```

---

### Products

#### List Products
```http
GET /api/v1/products
```

**Query Parameters:**
- `search` (optional): Search by product name
- `category_id` (optional): Filter by category
- `featured` (optional): Filter featured products
- `page` (optional): Page number
- `per_page` (optional): Items per page (max 100)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "name": "Product Name",
      "slug": "product-name",
      "description": "Product description",
      "price": 100000,
      "compare_price": 150000,
      "stock": 50,
      "sku": "PROD-001",
      "images": ["https://example.com/image1.jpg"],
      "is_active": true,
      "is_featured": false,
      "category": {
        "id": 1,
        "name": "Electronics"
      }
    }
  ],
  "current_page": 1,
  "per_page": 20,
  "total": 100
}
```

#### Get Product
```http
GET /api/v1/products/{id}
```

#### Get Products by Category
```http
GET /api/v1/products/category/{categoryId}
```

#### Create Product (Admin)
```http
POST /api/v1/admin/products
```

**Headers:**
- `Authorization: Bearer {token}`

**Body:**
```json
{
  "category_id": 1,
  "name": "Product Name",
  "description": "Product description",
  "price": 100000,
  "compare_price": 150000,
  "stock": 50,
  "sku": "PROD-001",
  "images": ["https://example.com/image1.jpg"],
  "specifications": {
    "color": "Black",
    "size": "Large"
  },
  "weight": 1.5,
  "is_active": true,
  "is_featured": false
}
```

#### Update Product (Admin)
```http
PUT /api/v1/admin/products/{id}
```

#### Delete Product (Admin)
```http
DELETE /api/v1/admin/products/{id}
```

---

### Shopping Cart

#### Get Cart
```http
GET /api/v1/cart
```

**Response:**
```json
{
  "cart": {
    "id": 1,
    "session_id": "abc123",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "quantity": 2,
        "price": 100000,
        "product": {
          "id": 1,
          "name": "Product Name",
          "images": ["https://example.com/image.jpg"]
        }
      }
    ]
  },
  "total": 200000
}
```

#### Add Item to Cart
```http
POST /api/v1/cart/add
```

**Body:**
```json
{
  "product_id": 1,
  "quantity": 2
}
```

**Response:**
```json
{
  "cart": {...},
  "total": 200000
}
```

#### Update Cart Item
```http
PUT /api/v1/cart/update/{itemId}
```

**Body:**
```json
{
  "quantity": 3
}
```

#### Remove Cart Item
```http
DELETE /api/v1/cart/remove/{itemId}
```

#### Clear Cart
```http
DELETE /api/v1/cart/clear
```

---

### Orders

#### Checkout
```http
POST /api/v1/orders/checkout
```

**Body:**
```json
{
  "customer_name": "John Doe",
  "customer_phone": "628123456789",
  "customer_email": "john@example.com",
  "customer_address": "Jl. Contoh No. 123, Jakarta",
  "shipping_cost": 20000,
  "notes": "Please deliver in the morning"
}
```

**Response:**
```json
{
  "order": {
    "id": 1,
    "order_number": "ORD-1699788000-1234",
    "customer_name": "John Doe",
    "customer_phone": "628123456789",
    "subtotal": 200000,
    "shipping_cost": 20000,
    "total": 220000,
    "status": "pending",
    "items": [
      {
        "id": 1,
        "product_name": "Product Name",
        "quantity": 2,
        "price": 100000,
        "subtotal": 200000
      }
    ]
  },
  "message": "Order created successfully. The store will contact you via WhatsApp shortly."
}
```

#### Get Order
```http
GET /api/v1/orders/{id}
```

#### List Orders (Admin)
```http
GET /api/v1/admin/orders
```

**Headers:**
- `Authorization: Bearer {token}`

**Query Parameters:**
- `status` (optional): Filter by status
- `page` (optional): Page number

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "order_number": "ORD-1699788000-1234",
      "customer_name": "John Doe",
      "total": 220000,
      "status": "pending",
      "created_at": "2025-11-12T10:00:00Z",
      "items": [...]
    }
  ]
}
```

#### Update Order Status (Admin)
```http
PUT /api/v1/admin/orders/{id}/status
```

**Headers:**
- `Authorization: Bearer {token}`

**Body:**
```json
{
  "status": "confirmed",
  "notes": "Order confirmed, will be shipped tomorrow"
}
```

**Response:**
```json
{
  "order": {...},
  "message": "Order status updated successfully"
}
```

**Status Values:**
- `pending`: Order created, waiting for confirmation
- `confirmed`: Order confirmed by store
- `processing`: Order is being prepared
- `shipped`: Order has been shipped
- `delivered`: Order delivered to customer
- `cancelled`: Order cancelled

**Note:** When status is updated, a WhatsApp notification is automatically sent to the customer.

---

## Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 201  | Created |
| 204  | No Content |
| 400  | Bad Request |
| 401  | Unauthorized |
| 403  | Forbidden |
| 404  | Not Found |
| 422  | Validation Error |
| 500  | Internal Server Error |

---

## Rate Limiting

API requests are rate-limited to prevent abuse:
- Public endpoints: 60 requests per minute
- Authenticated endpoints: 120 requests per minute

Rate limit headers are included in responses:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

---

## Webhooks (Future)

Webhook support for order events will be added in future versions:
- `order.created`
- `order.status_changed`
- `order.completed`

---

## SDKs and Libraries

Official SDKs coming soon:
- JavaScript/TypeScript
- PHP
- Python

---

## Support

For API support and questions:
- GitHub Issues: https://github.com/ardantus/mysaas/issues
- Email: support@yourdomain.com
