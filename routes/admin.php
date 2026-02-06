<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PriceRangeController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ProductController;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/admin
*/

Route::prefix('v1')->group(function () {

    // Public Routes
    Route::post('login', [AuthController::class, 'login']);
    Route::get('settings/logo', [SettingController::class, 'getPublicLogo']);

    // Protected Routes
    Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::put('me', [AuthController::class, 'updateProfile']);

        // Orders
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{id}', [OrderController::class, 'show']);
        Route::put('orders/{id}', [OrderController::class, 'update']);
        Route::delete('orders/{id}', [OrderController::class, 'destroy']);
        Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('orders/{id}/customer-details', [OrderController::class, 'updateCustomerDetails']);

        // Settings
        Route::get('settings', [SettingController::class, 'show']);
        Route::put('settings', [SettingController::class, 'update']);

        // Users
        Route::apiResource('users', UserController::class);

        // Categories
        Route::apiResource('categories', CategoryController::class);

        // Price Ranges
        Route::apiResource('price-ranges', PriceRangeController::class);

        // Brands
        Route::apiResource('brands', BrandController::class);

        // Coupons
        Route::apiResource('coupons', CouponController::class);

        // Banners
        Route::apiResource('banners', BannerController::class);

        // Products
        Route::get('products/options', [ProductController::class, 'options']);
        Route::apiResource('products', ProductController::class);
    });
});
