<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;




class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                 Forms\Components\TextInput::make('order_number')
            ->label('Order ID')
            ->disabled(),

        Forms\Components\TextInput::make('name')
            ->label('Customer Name')
            ->disabled(),

        Forms\Components\TextInput::make('order_date')
            ->label('Order Date')
            ->disabled(),

        Forms\Components\TextInput::make('status')
            ->label('Order Status')
            ->disabled(),

        Forms\Components\TextInput::make('items_count')
            ->label('Items')
            ->disabled(),

        Forms\Components\TextInput::make('total_amount')
            ->label('Total Amount')
            ->disabled(),

        Forms\Components\TextInput::make('paid_amount')
            ->label('Paid Amount')
            ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date('d-m-Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('items_count')   // ✅ Items added
                    ->label('Items')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Order Status')
                    ->colors([
                        'primary' => 'pending',
                        'warning' => 'active',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Paid')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'success' => 'paid',
                        'warning' => 'due',
                        'danger' => 'unpaid',
                    ])
                    ->sortable(),
            ])

            ->filters([
                //
            ])

       ->actions([
    Action::make('view')
        ->icon('heroicon-o-eye')
        ->label(false)
        ->modalHeading('Order Details')
        ->modalSubmitAction(false)
        ->modalCancelActionLabel('Close')
        ->modalContent(fn ($record) => view('filament.pages.view_order', [
            'record' => $record,
        ])),

   Action::make('edit')
    ->icon('heroicon-o-pencil')
    ->label(false)
->url(fn ($record) => url("/admin/edit-order/{$record->id}"))
    ->openUrlInNewTab(false),

    Action::make('delete')
        ->icon('heroicon-o-trash')
        ->label(false)
        ->requiresConfirmation()
        ->action(fn ($record) => $record->delete()),
])


          ->bulkActions([
    DeleteBulkAction::make(),
]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            // 'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}