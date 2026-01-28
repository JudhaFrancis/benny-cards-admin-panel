<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderTrackingController;


Route::get('/', function () {
    return redirect('/admin/login');
});


Route::post('/order-tracking/store', [OrderTrackingController::class, 'store'])
    ->name('order.tracking.store');