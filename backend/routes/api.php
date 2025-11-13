<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Central API routes (main domain)
Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('user', [AuthController::class, 'user'])->middleware('auth');
    
    // Store management routes (for store owners)
    Route::middleware(['auth'])->group(function () {
        Route::apiResource('stores', StoreController::class);
    });
});

// Tenant API routes (store-specific domains/subdomains)
Route::middleware(['tenant'])->prefix('v1')->group(function () {
    
    // Public routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);
    
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/products/category/{category}', [ProductController::class, 'byCategory']);
    
    // Cart routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'show']);
        Route::post('/add', [CartController::class, 'addItem']);
        Route::put('/update/{item}', [CartController::class, 'updateItem']);
        Route::delete('/remove/{item}', [CartController::class, 'removeItem']);
        Route::delete('/clear', [CartController::class, 'clear']);
    });
    
    // Order routes
    Route::prefix('orders')->group(function () {
        Route::post('/checkout', [OrderController::class, 'checkout']);
        Route::get('/{order}', [OrderController::class, 'show']);
    });
    
    // Store owner routes (authenticated)
    Route::middleware(['auth'])->group(function () {
        // Category management
        Route::apiResource('admin/categories', CategoryController::class)->except(['index', 'show']);
        
        // Product management
        Route::apiResource('admin/products', ProductController::class)->except(['index', 'show']);
        
        // Order management
        Route::prefix('admin/orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::put('/{order}/status', [OrderController::class, 'updateStatus']);
        });
    });
});
