# Backend Implementation

The backend is built with Laravel 10, serving as a RESTful API for the Vue 3 frontend. It emphasizes clean code, security, and scalability.

## Core Backend Components

### 1. Controllers (`app/Http/Controllers/Admin`)
Controllers are responsible for routing requests to the appropriate services. They use:
- **Form Requests:** For strict input validation (e.g., `app/Http/Requests`).
- **API Resources:** To transform models into consistent JSON formats.

### 2. Services Layer (`app/Services`)
Business logic is decoupled from controllers. For example:
- `OrderService.php`: Manages complex order creation, status updates, and tracking logic.
- `WhatsAppService.php`: Handles communication integrations.

### 3. Models & Relationships (`app/Models`)
The system uses Eloquent ORM with relationships:
- `Order` belongsTo `User`
- `Order` hasMany `OrderItem`
- `User` hasMany `Role` (via Spatie)

### 4. Authentication (Sanctum)
Authentication is handled via **Laravel Sanctum**. 
- Token-based authentication for stateful and stateless requests.
- `AuthController` manages login, logout, and profile retrieval.

### 5. Role-Based Access Control (Spatie)
Permissions are enforced at two levels:
- **Middleware:** Protecting API routes in `routes/api.php`.
- **Policy/Gate:** Enforcing logic within services or controllers.

### 6. Database Migrations
Database structure is versioned in `database/migrations`.
- Use `php artisan migrate` to sync changes.
- Seeders in `database/seeders` populate initial data and roles.

## API Structure

All admin-related routes are prefixed with `/api/v1/`.

Example Routing in `routes/api.php`:
```php
Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum']], function () {
    Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
    Route::apiResource('products', App\Http\Controllers\Admin\ProductController::class);
});
```

## Business Logic Flow Example: Order Creation
1. Request received at `OrderController@store`.
2. Input validated via `StoreOrderRequest`.
3. Controller calls `OrderService->createOrder($data)`.
4. `OrderService` initiates a `DB::transaction`.
5. Product stock updated, totals calculated, and database records created.
6. Controller returns `OrderResource`.

---
[Return to Architecture](architecture.md) | [Next: Frontend Implementation](frontend.md)
