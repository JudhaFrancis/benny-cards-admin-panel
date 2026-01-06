<div x-data="{
    open: false,
    section: 'order',
    sectionLabel: 'Order Details'
}" class="w-full max-w-3xl mx-auto">
    <!-- Title -->
    <h2 class="text-lg font-semibold mb-4">
        Edit Order - {{ $record->order_number ?? 'ORD-001' }}
    </h2>

    <!-- Label -->
    <label class="block text-sm font-medium text-gray-600 mb-2">
        Select Tracking Section
    </label>

    <!-- Dropdown Header (FULL WIDTH) -->
    <div @click="open = !open" class="flex items-center justify-between w-full px-4 py-3
                border border-gray-300 rounded-lg bg-white cursor-pointer">
        <div class="flex items-center gap-2 text-gray-800">
            <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-primary-600" />

            <span class="text-sm font-medium" x-text="sectionLabel"></span>

        </div>

        <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 transition-transform"
            x-bind:class="{ 'rotate-180': open }" />
    </div>

    <!-- Dropdown Content -->
    <div x-show="open" x-transition @click.outside="open = false"
        class="mt-2 w-full border border-gray-200 rounded-lg bg-white p-4 space-y-3">
        <!-- Order Details -->

        <div @click="section='order';sectionLabel='Order Details'; open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-primary-500" />
            <span>Order Details</span>
        </div>


        <!-- Client Information -->
        <div @click="section='client'; sectionLabel='Client Information';open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-user class="w-4 h-4 text-primary-500" />
            <span>Client Information</span>
        </div>

        <!-- Card Specifications -->
        <div @click="section='card'; sectionLabel='Card Specifications';open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-identification class="w-4 h-4 text-primary-500" />
            <span>Card Specifications</span>
        </div>


        <!-- Work Assign -->
        <div @click="section='work'; sectionLabel='Work Assign Process';open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-user-group class="w-4 h-4 text-primary-500" />
            <span>Work Assign Process</span>
        </div>

        <!-- Design -->
        <div @click="section='design'; sectionLabel='Design – Checked & Given to Print';open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-paint-brush class="w-4 h-4 text-primary-500" />
            <span>Design – Checked & Given to Print</span>
        </div>

        <div @click="section='printing';sectionLabel='Order & Printing Status'; open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-printer class="w-4 h-4 text-primary-500" />
            <span>Order & Printing Status</span>
        </div>
        <!-- Packaging -->
        <div @click="section='packaging'; sectionLabel='Packaging & Logistics';open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-archive-box class="w-4 h-4 text-primary-500" />
            <span>Packaging & Logistics</span>
        </div>


        <!-- Packaging Status -->
        <div @click="section='packaging_status';sectionLabel='Packaging Status'; open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-truck class="w-4 h-4 text-primary-500" />
            <span>Packaging Status</span>
        </div>
        <!-- Delivery Location -->
        <div @click="section='delivery_location';sectionLabel='Delivery Location'; open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-map-pin class="w-4 h-4 text-primary-500" />
            <span>Delivery Location</span>
        </div>

        <!-- Dispatch Mode -->
        <div @click="section='dispatch_mode'; sectionLabel='Mode of Dispatch';open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-paper-airplane class="w-4 h-4 text-primary-500" />
            <span>Mode of Dispatch</span>
        </div>

        <!-- Dispatch Details -->
        <div @click="section='dispatch_details'; sectionLabel='Dispatch Details';open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-truck class="w-4 h-4 text-primary-500" />
            <span>Dispatch Details</span>
        </div>


        <!-- Payment -->
        <div @click="section='payment'; sectionLabel='Payment';open=false"
            class="flex items-center gap-3 text-sm cursor-pointer hover:text-primary-600">
            <x-heroicon-o-credit-card class="w-4 h-4 text-primary-500" />
            <span>Payment</span>
        </div>

    </div>


    <div class="mt-6 mb-6 flex flex-wrap gap-3 w-full max-w-3xl mx-auto">

        <button @click.stop="section='order'"
            class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700 cursor-pointer">
            Order
        </button>

        <button @click.stop="section='client'"
            class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700 cursor-pointer">
            Client
        </button>

        <button @click.stop="section='card'"
            class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700 cursor-pointer">
            Card
        </button>

        <button @click.stop="section='work'"
            class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700 cursor-pointer">
            Work
        </button>


        <button @click.stop="section='design'"
            class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700 cursor-pointer">
            Design
        </button>
        <button @click.stop="section='printing'" class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700">
            Order
        </button>
        <button @click.stop="section='packaging'"
            class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700 cursor-pointer">
            Packaging
        </button>
        <button @click.stop="section='packaging_status'"
            class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700 hover:bg-teal-100 hover:text-teal-700 transition">
            Packaging
        </button>


        <button @click.stop="section='delivery_location'" class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700
           hover:bg-teal-100 hover:text-teal-700 transition">
            Delivery
        </button>
        <button @click.stop="section='dispatch_mode'" class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700
           hover:bg-teal-100 hover:text-teal-700 transition">
            Mode
        </button>


        <button @click.stop="section='dispatch_details'" class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700
           hover:bg-teal-100 hover:text-teal-700 transition">
            Dispatch
        </button>


        <button @click.stop="section='payment'" class="px-4 py-1.5 text-sm rounded-full bg-gray-100 text-gray-700
           hover:bg-teal-100 hover:text-teal-700 transition">
            Payment
        </button>

    </div>
    <!-- Order Details Section -->
<div x-show="section === 'order'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Section Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-clipboard-document class="w-5 h-5" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Order Details
        </h3>
    </div>
    <hr class="mb-4 mt-2">

    <!-- Form Grid -->
    <form method="POST" action="{{ route('order.tracking.store') }}">
        @csrf
        <!-- Hidden inputs -->
        <input type="hidden" name="orders_id" value="1">
        <input type="hidden" name="tracking_status_id" value="2">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 mt-2">

            <!-- Order No -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">Order No</label>
                <input type="text" name="order_number"
                    class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                    value="{{ $record->order_number }}" readonly>
            </div>

            <!-- Order Date -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">Order Date<span class="text-red-500">*</span></label>
                <input type="date" name="order_date"
                    class="w-full mt-1 rounded-lg border-gray-300 text-sm" required>
            </div>

            <!-- Order Taken By -->
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600 mb-2 block">Order Taken By<span class="text-red-500">*</span></label>
                <select name="order_taken_by" class="w-full mt-1 rounded-lg border-gray-300 text-sm" required>
                    <option value="" disabled selected>Select</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>
        </div>

        <!-- Order Placed In -->
        <div>
            <label class="text-sm text-gray-600 mb-2 mt-3 block">Order Placed In<span class="text-red-500">*</span></label>
            <div class="flex flex-wrap gap-4 text-sm">
                <label class="flex items-center gap-2">
                    <input type="radio" name="placed_in" value="NGL" required> NGL
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="placed_in" value="MTM"> MTM
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="placed_in" value="TVL"> TVL
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="placed_in" value="Chennai"> Chennai
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="placed_in" value="Online"> Online
                </label>
            </div>
        </div>

        <!-- Reference -->
        <div class="mt-3">
            <label class="text-sm text-gray-600 mb-2 block">Reference<span class="text-red-500">*</span></label>
            <div class="flex flex-wrap gap-4 text-sm mb-4">
                <label class="flex items-center gap-2">
                    <input type="radio" name="reference" value="Already Client" required> Already Client
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="reference" value="Instagram"> Instagram
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="reference" value="Walk-In"> Walk-In
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="reference" value="By Client"> By Client
                </label>
            </div>

            <input type="text" name="other_reference"
                   class="w-full mt-2 rounded-lg border-gray-300 text-sm"
                   placeholder="Other reference...">
        </div>

        <!-- Save Button -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>
    </form>

</div>


    <div x-show="section === 'client'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-user class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Client Information
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <form>

        <!-- Form Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 mt-2">

            <!-- Name -->
            <div class="flex flex-col gap-2">
                <label class="text-sm text-gray-600">
                    Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       class="rounded-lg border-gray-300 text-sm"
                       placeholder="John Doe"
                       required />
            </div>

            <!-- Place -->
            <div class="flex flex-col gap-2">
                <label class="text-sm text-gray-600">
                    Place <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       class="rounded-lg border-gray-300 text-sm"
                       placeholder="New York"
                       required />
            </div>

            <!-- Contact No -->
            <div class="flex flex-col gap-2">
                <label class="text-sm text-gray-600">
                    Contact No <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       class="rounded-lg border-gray-300 text-sm"
                       placeholder="+1 (555) 123-4567"
                       required />
            </div>

            <!-- Occasion -->
            <div class="flex flex-col gap-2">
                <label class="text-sm text-gray-600">
                    Occasion
                </label>
                <input type="text"
                       class="rounded-lg border-gray-300 text-sm"
                       placeholder="Wedding, Birthday, etc." required/>
            </div>

            <!-- Expected Delivery Date -->
            <div class="md:col-span-2 flex flex-col gap-2">
                <label class="text-sm text-gray-600">
                    Expected Delivery Date <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       class="rounded-lg border-gray-300 text-sm"
                       required />
            </div>
        </div>

        <!-- Save -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>

</div>


    <!-- Header -->

    <div x-show="section === 'card'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-identification class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Card Specifications
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <form>

        <!-- Card Type -->
        <div class="mb-4 mt-2">
            <label class="text-sm text-gray-600 mb-2 block">
                Card Type <span class="text-red-500">*</span>
            </label>

            <div class="flex flex-wrap items-center gap-4 sm:gap-10">
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="card_type" value="custom"
                           class="text-teal-600" required>
                    Customize Card
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="card_type" value="ready"
                           class="text-teal-600">
                    Ready Made Card
                </label>
            </div>
        </div>

        <!-- Form Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Card Size -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Card Size <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       placeholder="e.g. 5x7 inches"
                       required>
            </div>

            <!-- Quantity -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Quantity <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       min="1"
                       required>
            </div>

            <!-- Specifications -->
            <div class="md:col-span-2">
                <label class="text-sm text-gray-600 mb-2 block">
                    Specifications <span class="text-red-500">*</span>
                </label>
                <textarea rows="3"
                          class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                          placeholder="Enter specifications..." required></textarea>
            </div>

            <!-- Inner GSM -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Inner GSM <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"required>
            </div>

            <!-- Envelope GSM -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Envelope GSM <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"required>
            </div>

            <!-- Card Lamination -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Card Lamination <span class="text-red-500">*</span>
                </label>
                <select class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                        required>
                    <option value="" disabled selected>Select</option>
                    <option value="Matt">Matt</option>
                    <option value="Glossy">Glossy</option>
                </select>
            </div>

            <!-- Envelope Lamination -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Envelope Lamination <span class="text-red-500">*</span>
                </label>
                <select class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                        required>
                    <option value="" disabled selected>Select</option>
                    <option value="Matt">Matt</option>
                    <option value="Glossy">Glossy</option>
                </select>
            </div>

            <!-- Options -->
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700 block mb-2">
                    Options <span class="text-red-500">*</span>
                </label>

                <div
                    class="grid grid-cols-2 mt-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-4 gap-y-3 text-sm text-gray-700">

                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600" required> Sticker
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Band
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Satin Ribbon
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Rope
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Corner Cutting
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Envelope
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Insert Leaf
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Buttersheet
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Tag
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Org. Ribbon
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Foiling
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Screen Printing
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> UV
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> SC Offset
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> New Die
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Dry Flower / Fresh
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Ready Seal
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Special Paper
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="text-teal-600"> Custom Seal
                    </label>

                </div>
            </div>

        </div>

        <!-- Save Button -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>

</div>




    <!-- Work Assign Process -->
   <div x-show="section === 'work'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-4">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-user-group class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Work Assign Process
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <!-- ✅ FORM START -->
    <form>

        <!-- Status Options (at least one required) -->
        <div class="flex flex-wrap gap-6 mb-4 mt-2 text-sm text-gray-700">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="status[]"
                       class="text-teal-600 rounded"
                       required>
                Content Received
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="status[]" class="text-teal-600 rounded">
                Clear Content
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="status[]" class="text-teal-600 rounded">
                Tag
            </label>
        </div>

        <!-- Form Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Assigned To -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Assigned To <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       placeholder="Designer name"
                       required>
            </div>

            <!-- Deadline -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Deadline <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       required>
            </div>

            <!-- Content By -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Content By <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       placeholder="Enter name"
                       required>
            </div>

            <!-- Completed By -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Completed By <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       placeholder="Enter name"
                       required>
            </div>

        </div>

        <!-- Save Button -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>
    <!-- ✅ FORM END -->

</div>

    <!-- Design – Checked & Given to Print -->
    <div x-show="section === 'design'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-4">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-printer class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Design – Checked & Given to Print
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <!-- Note -->
    <p class="text-sm text-gray-600 mb-4 italic">
        Please <span class="text-green-600 font-medium">✔</span> whichever given to print
    </p>

    <!-- ✅ FORM START -->
    <form>

        <!-- Design Outputs -->
        <div class="mb-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">
                Design Outputs <span class="text-red-500">*</span>
            </h4>

            <div class="flex flex-wrap sm:flex-nowrap gap-6 text-sm text-gray-700 mt-3 mb-5">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="design_outputs[]"
                           class="text-teal-600 rounded"
                           required>
                    Invitation in Draft
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="design_outputs[]" class="text-teal-600 rounded">
                    Buttersheet / Master
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="design_outputs[]" class="text-teal-600 rounded">
                    Gift Frame
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="design_outputs[]" class="text-teal-600 rounded">
                    PDF
                </label>
            </div>
        </div>

        <!-- Print & Add-ons -->
        <div class="mb-2">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">
                Print & Add-ons <span class="text-red-500">*</span>
            </h4>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-y-3 text-sm text-gray-700 mt-2">

                <!-- At least one checkbox required -->
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]"
                           class="text-teal-600 rounded"
                           required> Sticker
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Band
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Satin Ribbon
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Rope
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Corner Cutting
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Envelope
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Insert Leaf
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Buttersheet
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Tag
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Org. Ribbon
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Foiling
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Screen Printing
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> UV
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Old Die
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> New Die
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Dry Flower
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Ready Seal
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Special Paper
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="print_addons[]" class="text-teal-600 rounded"> Custom Seal
                </label>

            </div>
        </div>

        <!-- Save -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>
    <!-- ✅ FORM END -->

</div>

    <!-- Order & Printing Status -->
    <div x-show="section === 'printing'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-printer class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Order & Printing Status
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <!-- ✅ FORM START -->
    <form>

        <!-- Assigned Date -->
        <div class="mb-4 mt-2">
            <label class="text-sm text-gray-600 mb-2 block">
                Assigned Date <span class="text-red-500">*</span>
            </label>
            <input type="date"
                   class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                   required>
        </div>

        <!-- Readymade Card -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mt-3">

            <h4 class="text-sm font-semibold text-gray-800 mb-4">
                Readymade Card
            </h4>

            <!-- Status (at least one required) -->
            <div class="flex flex-wrap gap-4 text-sm text-gray-700 mb-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="readymade_status[]"
                           class="text-teal-600 rounded"
                           required>
                    Ordered
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="readymade_status[]" class="text-teal-600 rounded">
                    Card Received
                </label>

                <label class="flex items-center gap-2">
                    <input type="checkbox" name="readymade_status[]" class="text-teal-600 rounded">
                    Sent to Print
                </label>
            </div>

            <!-- Follow Up -->
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">
                    Follow Up
                </p>

                <div class="flex flex-wrap gap-3 text-sm text-gray-700">
                    <template x-for="day in 7">
                        <label class="flex items-center gap-1">
                            <input type="radio"
                                   name="readymade_followup"
                                   class="text-teal-600"
                                   required>
                            Day <span x-text="day"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <!-- Customize Card -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-5 mt-3">

            <h4 class="text-sm font-semibold text-gray-800 mb-4">
                Customize Card
            </h4>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-sm text-gray-600 mb-2 block">
                        Sent to Print Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           class="w-full rounded-lg border-gray-300 text-sm"
                           required>
                </div>

                <div>
                    <label class="text-sm text-gray-600 mb-2 block">
                        Delivery Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           class="w-full rounded-lg border-gray-300 text-sm"
                           required>
                </div>
            </div>

            <!-- Follow Up -->
            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">
                    Follow Up 
                </p>

                <div class="flex flex-wrap gap-3 text-sm text-gray-700">
                    <template x-for="day in 7">
                        <label class="flex items-center gap-1">
                            <input type="radio"
                                   name="custom_followup"
                                   class="text-teal-600"
                                   required>
                            Day <span x-text="day"></span>
                        </label>
                    </template>
                </div>
            </div>
        </div>

        <!-- Printing Issues & Delay -->
        <div class="mt-4 grid grid-cols-1 gap-4">

            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Any Printing Issues <span class="text-red-500">*</span>
                </label>
                <textarea rows="3"
                          class="w-full rounded-lg border-gray-300 text-sm"
                          placeholder="Describe any issues..."
                          required></textarea>
            </div>

            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Delay Reason <span class="text-red-500">*</span>
                </label>
                <textarea rows="3"
                          class="w-full rounded-lg border-gray-300 text-sm"
                          placeholder="Reason for delay..."
                          required></textarea>
            </div>

        </div>

        <!-- Save -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>
    <!-- ✅ FORM END -->

</div>


    <!-- Packaging & Logistics -->
    <div x-show="section === 'packaging'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-archive-box class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Packaging & Logistics
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <!-- ✅ FORM START -->
    <form>

        <!-- Status (at least one required) -->
        <div class="flex flex-wrap gap-6 mb-4 mt-2 text-sm text-gray-700">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="packaging_status[]"
                       class="text-teal-600 rounded"
                       required>
                Card Received
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="packaging_status[]"
                       class="text-teal-600 rounded">
                Crafting Done
            </label>
        </div>

        <!-- Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">

            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Crafted By <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       placeholder="Enter name"
                       required>
            </div>

            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Names <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       placeholder="Names on cards"
                       required>
            </div>

            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       required>
            </div>

            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Qty of Cards <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       min="1"
                       class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                       required>
            </div>

        </div>

        <!-- Envelope Details -->
        <div>
            <label class="text-sm text-gray-600 mb-2 block">
                Envelope / Ribbon / Tag / Sticker <span class="text-red-500">*</span>
            </label>
            <textarea rows="3"
                      class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                      placeholder="Details..."
                      required></textarea>
        </div>

        <!-- Issues -->
        <div class="mt-3">
            <label class="text-sm text-gray-600 mb-2 block">
                Issues in Card <span class="text-red-500">*</span>
            </label>
            <textarea rows="3"
                      class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                      placeholder="Describe any issues..."
                      required></textarea>
        </div>

        <!-- Save -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>
    <!-- ✅ FORM END -->

</div>

    <!-- Packaging Status Section -->
    <div x-show="section === 'packaging_status'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-cube class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Packaging Status
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <!-- ✅ FORM START -->
    <form>

        <!-- Status Options (at least one required) -->
        <div class="flex flex-wrap gap-6 mb-4 mt-2 text-sm text-gray-700">
            <label class="flex items-center gap-2">
                <input type="checkbox"
                       name="packaging_status[]"
                       class="text-teal-600 rounded"
                       required>
                Packed with Gift
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox"
                       name="packaging_status[]"
                       class="text-teal-600 rounded">
                Packed without Gift
            </label>
        </div>

        <!-- Packed By -->
        <div class="mb-6">
            <label class="text-sm text-gray-600 mb-2 block">
                Packed By <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   class="w-full rounded-lg border-gray-300 text-sm"
                   placeholder="Enter name"
                   required>
        </div>

        <!-- Save Button -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>
    <!-- ✅ FORM END -->

</div>

    <!-- Delivery Location Section -->
    <div x-show="section === 'delivery_location'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-map-pin class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Delivery Location
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <!-- ✅ FORM START -->
    <form>

        <!-- Location Options -->
        <div class="flex flex-wrap gap-6 mb-4 mt-2 text-sm text-gray-700">
            <label class="flex items-center gap-2">
                <input type="radio"
                       name="delivery_location"
                       class="text-teal-600"
                       required>
                NGL Shop
            </label>

            <label class="flex items-center gap-2">
                <input type="radio"
                       name="delivery_location"
                       class="text-teal-600">
                Marthandam Shop
            </label>

            <label class="flex items-center gap-2">
                <input type="radio"
                       name="delivery_location"
                       class="text-teal-600">
                TVL Shop
            </label>

            <label class="flex items-center gap-2">
                <input type="radio"
                       name="delivery_location"
                       class="text-teal-600">
                Chennai Shop
            </label>
        </div>

        <!-- Place Name -->
        <div class="mb-6">
            <label class="text-sm text-gray-600 mb-2 block">
                Place Name <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   class="w-full rounded-lg border-gray-300 text-sm"
                   placeholder="Enter place name"
                   required>
        </div>

        <!-- Save -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>
    <!-- ✅ FORM END -->

</div>

    <!-- Mode of Dispatch Section -->
   <div x-show="section === 'dispatch_mode'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-paper-airplane class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Mode of Dispatch
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <!-- ✅ FORM START -->
    <form>

        <!-- Dispatch Options -->
        <div class="flex flex-wrap gap-6 mb-4 mt-2 text-sm text-gray-700">

            <label class="flex items-center gap-2">
                <input type="checkbox"
                       class="text-teal-600 rounded"
                       name="dispatch_mode[]"
                       required>
                Shop Pickup
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox"
                       class="text-teal-600 rounded"
                       name="dispatch_mode[]">
                Bus
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox"
                       class="text-teal-600 rounded"
                       name="dispatch_mode[]">
                Transport
            </label>

            <label class="flex items-center gap-2">
                <input type="checkbox"
                       class="text-teal-600 rounded"
                       name="dispatch_mode[]">
                Courier
            </label>

        </div>

        <!-- Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">

            <!-- Dispatch Date -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Dispatch Details with Date <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       class="w-full rounded-lg border-gray-300 text-sm"
                       required>
            </div>

            <!-- Expense -->
            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Dispatch Expense <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       class="w-full rounded-lg border-gray-300 text-sm"
                       placeholder="0.00"
                       required>
            </div>
        </div>

        <!-- Signature -->
        <div class="mb-6">
            <label class="text-sm text-gray-600 mb-2 block mt-3">
                Signature & Name <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   class="w-full rounded-lg border-gray-300 text-sm"
                   placeholder="Enter name"
                   required>
        </div>

        <!-- Save -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>
    <!-- ✅ FORM END -->

</div>

    <!-- Dispatch Details Section -->
   <div x-show="section === 'dispatch_details'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-truck class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Dispatch Details
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <!-- ✅ FORM START -->
    <form>

        <!-- Bus -->
        <div class="bg-gray-50 rounded-xl p-4 mb-4 mt-2">
            <h4 class="text-sm font-semibold text-gray-800 mb-2">Bus</h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm text-gray-600 mb-2 block">
                        Bus No <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                           required>
                </div>

                <div>
                    <label class="text-sm text-gray-600 mb-2 block">
                        Reaching Time <span class="text-red-500">*</span>
                    </label>
                    <input type="time"
                           class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                           required>
                </div>

                <div>
                    <label class="text-sm text-gray-600 mb-2 block">
                        Contact No <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                           required>
                </div>
            </div>
        </div>

        <!-- Courier -->
        <div class="bg-gray-50 rounded-xl p-3 mb-4">
            <h4 class="text-sm font-semibold text-gray-800 mb-2">Courier</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="text-sm text-gray-600 mb-2 block">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                           required>
                </div>

                <div>
                    <label class="text-sm text-gray-600 mb-2 block">
                        Tracking No <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                           required>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 mt-3">
                <input type="checkbox" class="text-teal-600 rounded">
                Shared in WhatsApp Group
            </label>
        </div>

        <!-- Transport -->
        <div class="bg-gray-50 rounded-xl p-4 mb-6">
            <h4 class="text-sm font-semibold text-gray-800 mb-2">Transport</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-gray-600 mb-2 block">
                        Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                           required>
                </div>

                <div>
                    <label class="text-sm text-gray-600 mb-2 block">
                        LR Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           class="w-full mt-1 rounded-lg border-gray-300 text-sm"
                           required>
                </div>
            </div>
        </div>

        <!-- Save -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>
    <!-- ✅ FORM END -->

</div>

    <!-- Payment Section -->
   <div x-show="section === 'payment'" x-transition
    class="mt-6 w-full max-w-3xl mx-auto border border-gray-200 rounded-xl bg-white p-6">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50">
            <x-heroicon-o-credit-card class="w-5 h-5 text-teal-600" />
        </div>
        <h3 class="text-base font-semibold text-gray-800">
            Payment
        </h3>
    </div>

    <hr class="mb-4 mt-2">

    <!-- ✅ FORM START -->
    <form>

        <!-- Amounts -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 mt-2">

            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Total Amount <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       class="w-full rounded-lg border-gray-300 text-sm"
                       placeholder="0.00"
                       required>
            </div>

            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Advance <span class="text-red-500">*</span>
                </label>
                <input type="number"
                       class="w-full rounded-lg border-gray-300 text-sm"
                       placeholder="0.00"
                       required>
            </div>

            <div>
                <label class="text-sm text-gray-600 mb-2 block">
                    Balance<span class="text-red-500">*</span>
                </label>
                <input type="number"
                       class="w-full rounded-lg border-gray-300 text-sm"
                       placeholder="0.00"
                       required>
            </div>

        </div>

        <!-- Payment Method -->
        <div class="mb-4">
            <label class="text-sm text-gray-600 mb-2 block">
                Payment Via <span class="text-red-500">*</span>
            </label>
            <select class="w-full rounded-lg border-gray-300 text-sm" required>
                <option>Cash</option>
                <option>UPI</option>
                <option>Card</option>
                <option>Bank Transfer</option>
            </select>
        </div>

        <!-- Signature -->
        <div class="mb-6">
            <label class="text-sm text-gray-600 mb-2 block">
                Signature & Name <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   class="w-full rounded-lg border-gray-300 text-sm"
                   placeholder="Enter name"
                   required>
        </div>

        <!-- Save -->
        <div class="mt-6">
            <x-filament::button type="submit" color="primary" class="w-full">
                Save Section
            </x-filament::button>
        </div>

    </form>
    <!-- ✅ FORM END -->

</div>


</div>