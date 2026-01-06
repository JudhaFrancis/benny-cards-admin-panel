<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderTrackingController;


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

Route::get('/', function () {
    return view('welcome');
});

Route::post('/order-tracking/store', [OrderTrackingController::class, 'store'])
    ->name('order.tracking.store');