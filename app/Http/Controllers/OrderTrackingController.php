<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderTracking;
use Illuminate\Support\Facades\Log;



class OrderTrackingController extends Controller
{
    //
    public function store(Request $request)
{
    Log::info($request->all()); 

    OrderTracking::create([
        'orders_id' => $request->orders_id,
        'tracking_status_id' => $request->tracking_status_id,
        // 'tracking_details' => $request->except('_token', 'orders_id', 'tracking_status_id'),
    ]);

    return back()->with('success', 'Tracking inserted successfully');
}
   
}
