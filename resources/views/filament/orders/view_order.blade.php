<div class="space-y-6">

    <!-- Title -->
    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
        <span class="text-2xl">📦</span>
        Order Details
    </h2>

    <!-- FIXED HORIZONTAL ORDER DETAILS -->
    <div class="flex flex-row justify-between text-gray-800 w-full gap-12">

        <div class="w-1/4">
            <p class="text-sm font-semibold"># Order ID</p>
            <p class="mt-1 break-words">{{ $record->order_number }}</p>
        </div>

        <div class="w-1/4">
            <p class="text-sm font-semibold">Order Date</p>
            <p class="mt-1 break-words">{{ $record->order_date }}</p>
        </div>

        <div class="w-1/4">
            <p class="text-sm font-semibold">Tracking ID</p>
            <p class="mt-1 break-words">{{ $record->tracking_id }}</p>
        </div>

        <div class="w-1/4">
            <p class="text-sm font-semibold">Status</p>
            <p class="mt-1 break-words">{{ ucfirst($record->status) }}</p>
        </div>

    </div>


    <!-- ORDER PROGRESS (same as screenshot 2 style) -->
    <div class="border-t pt-6">

        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            <span class="text-xl">🚚</span>
            Order Progress
        </h3>

        <div class="mt-6 space-y-10">

            <!-- Order Confirmed -->
            <div class="flex gap-4 relative">
                <!-- Circle -->
                <div>
                    <div class="w-4 h-4 rounded-full bg-green-500"></div>
                    <!-- Vertical Line -->
                    <div class="absolute left-2 top-4 w-[2px] h-full bg-green-500"></div>
                </div>

                <div>
                    <p class="font-semibold">Order Confirmed</p>
                    <p class="text-gray-500 text-sm">{{ $record->order_date }}</p>
                    <p class="text-gray-600 text-sm">Your order has been confirmed.</p>
                </div>
            </div>

            <!-- Processing -->
            <div class="flex gap-4 relative">
                <div>
                    <div class="w-4 h-4 rounded-full bg-green-500"></div>
                    <div class="absolute left-2 top-4 w-[2px] h-full bg-green-500"></div>
                </div>

                <div>
                    <p class="font-semibold">Processing</p>
                    <p class="text-gray-500 text-sm">Processing date here</p>
                    <p class="text-gray-600 text-sm">Your items are being prepared.</p>
                </div>
            </div>

            <!-- Shipped -->
            <div class="flex gap-4 relative">
                <div>
                    <div class="w-4 h-4 rounded-full bg-green-500"></div>
                    <div class="absolute left-2 top-4 w-[2px] h-full bg-green-500"></div>
                </div>

                <div>
                    <p class="font-semibold">Shipped</p>
                    <p class="text-gray-500 text-sm">Shipped date here</p>
                    <p class="text-gray-600 text-sm">Your package is on its way.</p>
                </div>
            </div>

            <!-- Out for Delivery -->
            <div class="flex gap-4 relative">
                <div>
                    <div class="w-4 h-4 rounded-full border border-gray-400"></div>
                    <div class="absolute left-2 top-4 w-[2px] h-full bg-gray-300"></div>
                </div>

                <div>
                    <p class="font-semibold">Out for Delivery</p>
                    <p class="text-gray-500 text-sm">Out for delivery date here</p>
                    <p class="text-gray-600 text-sm">Your package will arrive today.</p>
                </div>
            </div>

            <!-- Delivered (last one — NO LINE BELOW) -->
            <div class="flex gap-4 relative">
                <div>
                    <div class="w-4 h-4 rounded-full border border-gray-400"></div>
                </div>

                <div>
                    <p class="font-semibold">Delivered</p>
                    <p class="text-gray-600 text-sm">Package delivered successfully.</p>
                </div>
            </div>

        </div>

    </div>

    <!-- ======================= ORDER ITEMS ======================== -->
<div class="border-t pt-6">
    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
        <span class="text-xl"></span>
        Order Items
    </h3>

    <div class="mt-4 space-y-4"> <!-- Changed from space-y-10 to space-y-4 -->

        @forelse(($record->items ?? []) as $item)
            <div class="p-4 bg-gray-100 rounded-lg flex justify-between items-center">

                <div>
                    <p class="font-semibold text-gray-900">{{ $item->product->title ?? 'Unknown Product' }}</p>
                    <p class="text-gray-600 text-sm">Qty: {{ $item->quantity }}</p>
                </div>

                <p class="font-semibold text-gray-700">
                    ${{ number_format($item->quantity * $item->final_amount, 2) }}
                </p>

            </div>
        @empty
            <p class="text-gray-500 text-sm">No items found for this order.</p>
        @endforelse

    </div>
</div>


    <!-- ======================= DELIVERY ADDRESS ======================== -->
    <div class="border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2"> <span class="text-xl">📍</span>
            Delivery Address </h3>
        <div class="mt-3 p-3 rounded-lg bg-gray-100 text-gray-700 leading-relaxed mt-6 space-y-10">
            <p class="font-semibold text-gray-900">{{ $record->name }}</p>
            <p>{{ $record->address_1 }}</p> @if($record->address_2)
            <p>{{ $record->address_2 }}</p> @endif <p>{{ $record->country }}, {{ $record->post_code }}</p>
            @if($record->phone)
            <p class="mt-2">{{ $record->phone }}</p> @endif
        </div>
    </div> <!-- ======================= TOTAL AMOUNT FOOTER ======================== -->
    <div class="border-t pt-6">
        <div class="p-4 bg-red-50 rounded-lg flex justify-between items-center">
            <p class="text-lg font-semibold text-gray-700">Total Amount</p>
            <p class="text-xl font-bold text-red-600"> ${{ number_format($record->total_amount, 2) }} </p>
        </div>
    </div>

</div>