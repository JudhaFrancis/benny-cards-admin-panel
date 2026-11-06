<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/debug-config', function () {
    $role = new \Spatie\Permission\Models\Role();
    $related = $role->permissions()->getRelated();
    return response()->json([
        'config_permission_model' => config('permission.models.permission'),
        'role_permissions_related_class' => get_class($related),
        'related_has_module_method' => method_exists($related, 'module'),
    ]);
});

Route::get('/{any}', function () {
    return view('admin');
})->where('any', '.*');
