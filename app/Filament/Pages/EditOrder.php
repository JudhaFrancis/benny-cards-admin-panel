<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Checkbox;
use BackedEnum;
use Filament\Forms\Components\ToggleButtons;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

use App\Models\OrderTracking;

class EditOrder extends Page
{
    use InteractsWithForms;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Pencil;

    protected string $view = 'filament.pages.edit-order';
    public string $section = 'order';
    protected static ?string $slug = 'edit-order/{record}';
    protected static ?string $title = '';
    public ?array $formData = [];


    protected static bool $shouldRegisterNavigation = false;
    protected array $sectionStatusMap = [
        'order' => 1,
        'client' => 2,
        'card' => 3,
        'work' => 4,
        'design' => 5,
        'printing' => 6,
        'packaging' => 7,
        'packaging_status' => 8,
        'delivery_location' => 9,
        'dispatch_mode' => 10,
        'dispatch_details' => 11,
        'payment' => 12,
    ];
    protected array $sectionFields = [

        'order' => [
            'order_date',
            'order_taken_by',
            'placed_in',
            'reference',
            'other_reference',
        ],

        'client' => [
            'client_name',
            'client_place',
            'client_mobile',
            'occasion',
            'expected_delivery_date',
        ],

        'card' => [
            'card_type',
            'card_size',
            'quantity',
            'specifications',
            'inner_gsm',
            'envelope_gsm',
            'card_lamination',
            'envelope_lamination',
            'options',
        ],

        'work' => [
            'work_status',
            'assigned_to',
            'deadline',
            'content_by',
            'completed_by',
        ],

        'design' => [
            'design_outputs',
            'print_addons',
        ],

        'printing' => [
            'assigned_date',
            'readymade_status',
            'readymade_followup',
            'custom_sent_to_print_date',
            'custom_delivery_date',
            'custom_followup',
            'printing_issues',
            'delay_reason',
        ],

        'packaging' => [
            'packaging_status',
            'crafted_by',
            'card_names',
            'packaging_date',
            'card_quantity',
            'packaging_material_details',
            'card_issues',
        ],

        'packaging_status' => [
            'final_packaging_status',
            'packed_by',
        ],

        'delivery_location' => [
            'delivery_location',
            'delivery_place_name',
        ],

        'dispatch_mode' => [
            'dispatch_mode',
            'dispatch_date',
            'dispatch_expense',
            'dispatch_signature',
        ],

        'dispatch_details' => [
            'bus_no',
            'bus_reaching_time',
            'bus_contact_no',
            'courier_name',
            'courier_tracking_no',
            'courier_shared_whatsapp',
            'transport_name',
            'transport_lr_number',
        ],

        'payment' => [
            'advance',
            'balance',
            'payment_via',
            'signature_name',
        ],
    ];

    public Order $record;

    public function mount(Order $record): void
    {
        $this->record = $record;

        $formData = [];

        $tracking = OrderTracking::where('orders_id', $record->id)
            ->where('tracking_status_id', 1) // order section
            ->first();

        if ($tracking && is_array($tracking->tracking_details)) {
            $formData = $tracking->tracking_details;
        }

        $this->form->fill($formData);
    }
    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('formData')
            ->schema([
                ToggleButtons::make('section')
                    ->hiddenLabel()
                    ->options([
                        'order' => 'Order',
                        'client' => 'Client',
                        'card' => 'Card',
                        'work' => 'Work',
                        'design' => 'Design',
                        'printing' => 'Printing',
                        'packaging' => 'Packaging',
                        'packaging_status' => 'Packaging Status',
                        'delivery_location' => 'Delivery',
                        'dispatch_mode' => 'Mode',
                        'dispatch_details' => 'Dispatch',
                        'payment' => 'Payment',
                    ])
                    ->inline()
                    ->reactive()
                    ->statePath('section')
                    ->columnSpan('full'),
                Section::make('Order Details')
                    ->icon(Heroicon::ClipboardDocumentList)
                    ->iconColor('primary')
                    ->schema([

                        TextInput::make('order_number')
                            ->label('Order No')
                            ->dehydrated(false),

                        DatePicker::make('order_date')
                            ->label('Order Date'),

                        TextInput::make('order_taken_by')
                            ->label('Order Taken By'),

                        Radio::make('placed_in')
                            ->label('Order Placed In')
                            ->options([
                                'NGL' => 'NGL',
                                'MTM' => 'MTM',
                                'TVL' => 'TVL',
                                'Chennai' => 'Chennai',
                                'Online' => 'Online',
                            ])
                            ->inline(),

                        Radio::make('reference')
                            ->label('Reference')
                            ->options([
                                'Already Client' => 'Already Client',
                                'Instagram' => 'Instagram',
                                'Walk-In' => 'Walk-In',
                                'By Client' => 'By Client',
                            ])
                            ->inline(),

                        TextInput::make('other_reference')
                            ->hiddenLabel()
                            ->placeholder('Other reference')
                    ])
                    ->visible(fn() => $this->section === 'order'),
                Section::make('Client Information')
                    ->icon(Heroicon::User)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([

                        // Row 1
                        TextInput::make('client_name')
                            ->label('Name'),

                        TextInput::make('client_place')
                            ->label('Place'),

                        // Row 2
                        TextInput::make('client_mobile')
                            ->label('Contact No')
                            ->tel(),

                        TextInput::make('occasion')
                            ->label('Occasion')
                            ->placeholder('Wedding, Birthday, etc.'),

                        // Row 3 (full width)
                        DatePicker::make('expected_delivery_date')
                            ->label('Expected Delivery Date')
                            ->columnSpan(2),

                    ])

                    ->visible(fn() => $this->section === 'client'),

                Section::make('Card Specifications')
                    ->icon(Heroicon::Identification)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([

                        // Card Type
                        Radio::make('card_type')
                            ->hiddenLabel()
                            ->options([
                                'custom' => 'Customize Card',
                                'ready' => 'Ready Made Card',
                            ])
                            ->inline()
                            ->required()
                            ->columnSpan(2),

                        // Card Size
                        TextInput::make('card_size')
                            ->label('Card Size')
                            ->placeholder('e.g. 5x7 inches')
                            ->required(),

                        // Quantity
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        // Specifications
                        TextInput::make('specifications')
                            ->label('Specifications')
                            ->columnSpan(2)
                            ->required(),

                        // Inner GSM
                        TextInput::make('inner_gsm')
                            ->label('Inner GSM')
                            ->numeric()
                            ->required(),

                        // Envelope GSM
                        TextInput::make('envelope_gsm')
                            ->label('Envelope GSM')
                            ->numeric()
                            ->required(),

                        // Card Lamination
                        Select::make('card_lamination')
                            ->label('Card Lamination')
                            ->options([
                                'Matt' => 'Matt',
                                'Glossy' => 'Glossy',
                            ])
                            ->required(),

                        // Envelope Lamination
                        Select::make('envelope_lamination')
                            ->label('Envelope Lamination')
                            ->options([
                                'Matt' => 'Matt',
                                'Glossy' => 'Glossy',
                            ])
                            ->required(),

                        // Options (Checkboxes)
                        CheckboxList::make('options')
                            ->label('Options')
                            ->options([
                                'Sticker' => 'Sticker',
                                'Corner Cutting' => 'Corner Cutting',
                                'Tag' => 'Tag',
                                'UV' => 'UV',
                                'Ready Seal' => 'Ready Seal',
                                'Band' => 'Band',
                                'Envelope' => 'Envelope',
                                'Org Ribbon' => 'Org. Ribbon',
                                'SC Offset' => 'SC Offset',
                                'Special Paper' => 'Special Paper',
                                'Satin Ribbon' => 'Satin Ribbon',
                                'Insert Leaf' => 'Insert Leaf',
                                'Foiling' => 'Foiling',
                                'New Die' => 'New Die',
                                'Custom Seal' => 'Custom Seal',
                                'Rope' => 'Rope',
                                'Buttersheet' => 'Buttersheet',
                                'Screen Printing' => 'Screen Printing',
                                'Dry Flower/Fresh' => 'Dry Flower / Fresh',

                            ])
                            ->columns(4)
                            ->required()
                            ->columnSpan(2),

                    ])
                    ->visible(fn() => $this->section === 'card'),
                Section::make('Work Assign Process')
                    ->icon(Heroicon::UserGroup)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([

                        CheckboxList::make('work_status')
                            ->hiddenLabel()->options([
                                    'content_received' => 'Content Received',
                                    'clear_content' => 'Clear Content',
                                    'tag' => 'Tag',
                                ])
                            ->columns(3)
                            ->minItems(1) // at least one required
                            ->required()
                            ->columnSpan(2),

                        // Assigned To
                        TextInput::make('assigned_to')
                            ->label('Assigned To')
                            ->placeholder('Designer name')
                            ->required(),

                        // Deadline
                        DatePicker::make('deadline')
                            ->label('Deadline')
                            ->required(),

                        // Content By
                        TextInput::make('content_by')
                            ->label('Content By')
                            ->placeholder('Enter name')
                            ->required(),

                        // Completed By
                        TextInput::make('completed_by')
                            ->label('Completed By')
                            ->placeholder('Enter name')
                            ->required(),

                    ])
                    ->visible(fn() => $this->section === 'work'),
                Section::make('Design – Checked & Given to Print')
                    ->icon(Heroicon::Printer)
                    ->iconColor('primary')
                    ->schema([

                        // Design Outputs - checkboxes (at least one required)
                        CheckboxList::make('design_outputs')
                            ->label('Design Outputs')
                            ->options([
                                'invitation_draft' => 'Invitation in Draft',
                                'buttersheet_master' => 'Buttersheet / Master',
                                'gift_frame' => 'Gift Frame',
                                'pdf' => 'PDF',
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->required(),

                        // Print & Add-ons - checkboxes (at least one required)
                        CheckboxList::make('print_addons')
                            ->label('Print & Add-ons')
                            ->options([
                                'Sticker' => 'Sticker',
                                'Corner Cutting' => 'Corner Cutting',
                                'Tag' => 'Tag',
                                'UV' => 'UV',
                                'Ready Seal' => 'Ready Seal',
                                'Band' => 'Band',
                                'Envelope' => 'Envelope',
                                'Org Ribbon' => 'Org. Ribbon',
                                'SC Offset' => 'SC Offset',
                                'Special Paper' => 'Special Paper',
                                'Satin Ribbon' => 'Satin Ribbon',
                                'Insert Leaf' => 'Insert Leaf',
                                'Foiling' => 'Foiling',
                                'New Die' => 'New Die',
                                'Custom Seal' => 'Custom Seal',
                                'Rope' => 'Rope',
                                'Buttersheet' => 'Buttersheet',
                                'Screen Printing' => 'Screen Printing',
                                'Dry Flower/Fresh' => 'Dry Flower / Fresh',
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->required(),
                    ])
                    ->visible(fn() => $this->section === 'design'),
                Section::make('Order & Printing Status')
                    ->icon(Heroicon::Printer)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([

                        // Assigned Date - full width
                        DatePicker::make('assigned_date')
                            ->label('Assigned Date')
                            ->required()
                            ->columnSpan(2),

                        // Readymade Card - full width (span both columns)
                        Section::make('Readymade Card')
                            ->schema([
                                CheckboxList::make('readymade_status')
                                    ->hiddenLabel()
                                    ->options([
                                        'ordered' => 'Ordered',
                                        'card_received' => 'Card Received',
                                        'sent_to_print' => 'Sent to Print',
                                    ])
                                    ->columns(3)
                                    ->minItems(1)
                                    ->required(),

                                Radio::make('readymade_followup')
                                    ->label('Follow Up')
                                    ->options([
                                        1 => 'Day 1',
                                        2 => 'Day 2',
                                        3 => 'Day 3',
                                        4 => 'Day 4',
                                        5 => 'Day 5',
                                        6 => 'Day 6',
                                        7 => 'Day 7',
                                    ])
                                    ->inline()
                                    ->required(),
                            ])
                            ->columnSpan(2),  // <-- span full width

                        Section::make('Customize Card')
                            ->columns(2)
                            ->schema([
                                DatePicker::make('custom_sent_to_print_date')
                                    ->label('Sent to Print Date')
                                    ->required()
                                    ->columnSpan(1),

                                DatePicker::make('custom_delivery_date')
                                    ->label('Delivery Date')
                                    ->required()
                                    ->columnSpan(1),

                                Radio::make('custom_followup')
                                    ->label('Follow Up')
                                    ->options([
                                        1 => 'Day 1',
                                        2 => 'Day 2',
                                        3 => 'Day 3',
                                        4 => 'Day 4',
                                        5 => 'Day 5',
                                        6 => 'Day 6',
                                        7 => 'Day 7',
                                    ])
                                    ->inline()
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columnSpan(2),

                        TextArea::make('printing_issues')
                            ->label('Any Printing Issues')
                            ->placeholder('Describe any issues...')
                            ->rows(3)
                            ->columnSpan(2)
                            ->required(),

                        TextArea::make('delay_reason')
                            ->label('Delay Reason')
                            ->placeholder('Reason for delay...')
                            ->rows(3)
                            ->columnSpan(2)
                            ->required(),

                    ])
                    ->visible(fn() => $this->section === 'printing'),

                Section::make('Packaging & Logistics')
                    ->icon(Heroicon::ArchiveBox)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([

                        CheckboxList::make('packaging_status')
                            ->hiddenLabel()
                            ->options([
                                'card_received' => 'Card Received',
                                'crafting_done' => 'Crafting Done',
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->required()
                            ->columnSpan(2),

                        // Crafted By
                        TextInput::make('crafted_by')
                            ->label('Crafted By')
                            ->placeholder('Enter name')
                            ->required(),

                        // Names on cards
                        TextInput::make('card_names')
                            ->label('Names')
                            ->placeholder('Names on cards')
                            ->required(),

                        // Date
                        DatePicker::make('packaging_date')
                            ->label('Date')
                            ->required(),

                        // Quantity
                        TextInput::make('card_quantity')
                            ->label('Qty of Cards')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        // Envelope / Ribbon / Tag / Sticker
                        Textarea::make('packaging_material_details')
                            ->label('Envelope / Ribbon / Tag / Sticker')
                            ->placeholder('Details...')
                            ->rows(3)
                            ->required()
                            ->columnSpan(2),

                        // Issues
                        Textarea::make('card_issues')
                            ->label('Issues in Card')
                            ->placeholder('Describe any issues...')
                            ->rows(3)
                            ->required()
                            ->columnSpan(2),

                    ])
                    ->visible(fn() => $this->section === 'packaging'),

                Section::make('Packaging Status')
                    ->icon(Heroicon::Cube)
                    ->iconColor('primary')
                    ->schema([

                        CheckboxList::make('final_packaging_status')
                            ->hiddenLabel()
                            ->options([
                                'Packed with Gift' => 'Packed with Gift',
                                'Packed without Gift' => 'Packed without Gift',
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->required(),

                        // Packed By
                        TextInput::make('packed_by')
                            ->label('Packed By')
                            ->placeholder('Enter name')
                            ->required(),

                    ])
                    ->visible(fn() => $this->section === 'packaging_status'),
                Section::make('Delivery Location')
                    ->icon(Heroicon::MapPin)
                    ->iconColor('primary')
                    ->schema([

                        Radio::make('delivery_location')
                            ->hiddenLabel()
                            ->options([
                                'ngl_shop' => 'NGL Shop',
                                'marthandam_shop' => 'Marthandam Shop',
                                'tvl_shop' => 'TVL Shop',
                                'chennai_shop' => 'Chennai Shop',
                            ])
                            ->inline(),

                        TextInput::make('delivery_place_name')
                            ->label('Place Name')
                            ->placeholder('Enter place name')

                    ])
                    ->visible(fn() => $this->section === 'delivery_location'),
                Section::make('Mode of Dispatch')
                    ->icon(Heroicon::PaperAirplane)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([

                        CheckboxList::make('dispatch_mode')
                            ->hiddenLabel()
                            ->options([
                                'shop_pickup' => 'Shop Pickup',
                                'bus' => 'Bus',
                                'transport' => 'Transport',
                                'courier' => 'Courier',
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->required()
                            ->columnSpan(2),

                        // Dispatch Date
                        DatePicker::make('dispatch_date')
                            ->label('Dispatch Details with Date')
                            ->required(),

                        // Dispatch Expense
                        TextInput::make('dispatch_expense')
                            ->label('Dispatch Expense')
                            ->numeric()
                            ->placeholder('0.00')
                            ->required(),

                        // Signature & Name
                        TextInput::make('dispatch_signature')
                            ->label('Signature & Name')
                            ->placeholder('Enter name')
                            ->required()
                            ->columnSpan(2),

                    ])
                    ->visible(fn() => $this->section === 'dispatch_mode'),
                Section::make('Dispatch Details')
                    ->icon(Heroicon::Truck)
                    ->iconColor('primary')
                    ->schema([

                        Section::make('Bus')
                            ->schema([
                                TextInput::make('bus_no')
                                    ->label('Bus No')
                                    ->required(),

                                TextInput::make('bus_reaching_time')
                                    ->label('Reaching Time')
                                    ->type('time')
                                    ->required(),

                                TextInput::make('bus_contact_no')
                                    ->label('Contact No')
                                    ->tel()
                                    ->required(),
                            ])
                            ->columns(3)
                            ->collapsible(),

                        Section::make('Courier')
                            ->schema([
                                TextInput::make('courier_name')
                                    ->label('Name')
                                    ->required(),

                                TextInput::make('courier_tracking_no')
                                    ->label('Tracking No')
                                    ->required(),

                                Checkbox::make('courier_shared_whatsapp')
                                    ->label('Shared in WhatsApp Group'),
                            ])
                            ->columns(2)
                            ->collapsible(),

                        Section::make('Transport')
                            ->schema([
                                TextInput::make('transport_name')
                                    ->label('Name')
                                    ->required(),

                                TextInput::make('transport_lr_number')
                                    ->label('LR Number')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsible(),

                    ])
                    ->visible(fn() => $this->section === 'dispatch_details'),
                Section::make('Payment')
                    ->icon(Heroicon::CreditCard)
                    ->iconColor('primary')
                    ->columns(3)
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->placeholder('0.00')
                            ->disabled()
                            ->default('$' . $this->record->total_amount)
                            ->required(),

                        TextInput::make('advance')
                            ->label('Advance')
                            ->numeric()
                            ->placeholder('0.00')
                            ->required(),

                        TextInput::make('balance')
                            ->label('Balance')
                            ->numeric()
                            ->placeholder('0.00')
                            ->required(),

                        Select::make('payment_via')
                            ->label('Payment Via')
                            ->options([
                                'Cash' => 'Cash',
                                'UPI' => 'UPI',
                                'Card' => 'Card',
                                'Bank Transfer' => 'Bank Transfer',
                            ])
                            ->required()
                            ->columnSpan(3),

                        TextInput::make('signature_name')
                            ->label('Signature & Name')
                            ->placeholder('Enter name')
                            ->required()
                            ->columnSpan(3),
                    ])
                    ->visible(fn() => $this->section === 'payment'),

                Action::make('Save Section')
                    ->button()
                    ->label('Save Section')
                    ->submit('save')
                    ->color('primary'),
            ]);
    }

    public function save(): void
    {
        // Get all form data
        $data = $this->form->getState();

        // Only save fields for the current section
        $sectionData = [];
        foreach ($this->sectionFields[$this->section] as $field) {
            if (isset($data[$field])) {
                $sectionData[$field] = $data[$field];
            }
        }

        // Find existing OrderTracking record or create new
        $tracking = OrderTracking::firstOrNew([
            'orders_id' => $this->record->id,
            'tracking_status_id' => $this->sectionStatusMap[$this->section], // e.g., 1 for 'order'
        ]);

        // Save section data as JSON
        $tracking->tracking_details = $sectionData;
        $tracking->save();

        // Notification
        Notification::make()
            ->title('Section saved successfully!')
            ->success()
            ->send();
    }
}