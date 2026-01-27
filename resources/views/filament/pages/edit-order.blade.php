<x-filament::page heading="false">
    <div class="px-6 py-6 space-y-6" x-data="{
        open: false,
        section: @entangle('section').live,
        sectionLabel: 'Order Details'
    }">
        <h1 class="text-2xl font-bold text-gray-800 mt-6 mb-4">
            Edit Order - {{ $record->order_number ?? 'ORD-001' }}
        </h1>

        <label class="block text-sm font-medium text-gray-600 mb-2 mt-4">
            Select Tracking Section
        </label>
        <!-- Dropdown Header + Content -->
        <div @click="open = !open" class="flex items-center justify-between w-full px-4 py-3
               border border-gray-300 rounded-lg bg-white cursor-pointer">
            <div class="flex items-center gap-3 text-gray-800">
                                <x-filament::dropdown.list.item icon="heroicon-m-clipboard-document-list" icon-color="primary"
>
                       <span x-text="sectionLabel"></span>
                </x-filament::dropdown.list.item>

            </div>
        </div>

        <div x-show="open" x-transition @click.outside="open = false"
            class="mt-2 w-full border border-gray-200 rounded-lg bg-white">
            <x-filament::dropdown.list>
                <x-filament::dropdown.list.item icon="heroicon-m-clipboard-document-list" icon-color="primary"
                    @click="section='order'; sectionLabel='Order Details'; open=false">
                    Order Details
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-user" icon-color="primary"
                    @click="section='client'; sectionLabel='Client Information'; open=false">
                    Client Information
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-identification" icon-color="primary"
                    @click="section='card'; sectionLabel='Card Specifications'; open=false">
                    Card Specifications
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-user-group" icon-color="primary"
                    @click="section='work'; sectionLabel='Work Assign Process'; open=false">
                    Work Assign Process
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-paint-brush" icon-color="primary"
                    @click="section='design'; sectionLabel='Design – Checked & Given to Print'; open=false">
                    Design – Checked & Given to Print
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-printer" icon-color="primary"
                    @click="section='printing'; sectionLabel='Order & Printing Status'; open=false">
                    Order & Printing Status
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-archive-box" icon-color="primary"
                    @click="section='packaging'; sectionLabel='Packaging & Logistics'; open=false">
                    Packaging & Logistics
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-truck" icon-color="primary"
                    @click="section='packaging_status'; sectionLabel='Packaging Status'; open=false">
                    Packaging Status
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-map-pin" icon-color="primary"
                    @click="section='delivery_location'; sectionLabel='Delivery Location'; open=false">
                    Delivery Location
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-paper-airplane" icon-color="primary"
                    @click="section='dispatch_mode'; sectionLabel='Mode of Dispatch'; open=false">
                    Mode of Dispatch
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-truck" icon-color="primary"
                    @click="section='dispatch_details'; sectionLabel='Dispatch Details'; open=false">
                    Dispatch Details
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-m-credit-card" icon-color="primary"
                    @click="section='payment'; sectionLabel='Payment'; open=false">
                    Payment
                </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
        </div>

        <!-- Buttons -->
       

        <!-- Form -->
       <form wire:submit.prevent="save">
    {{ $this->form }}
</form>

    </div>
</x-filament::page>