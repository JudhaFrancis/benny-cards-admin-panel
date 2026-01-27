<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderTrackingController;


Route::get('/', function () {
    return view('welcome');
});


Route::post('/order-tracking/store', [OrderTrackingController::class, 'store'])
    ->name('order.tracking.store');