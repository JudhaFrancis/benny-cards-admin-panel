<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Livewire\Component;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Navigation\NavigationItem;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Schemas\Schema;
use UnitEnum;
use BackedEnum;



class UserResource extends Resource
{
    protected static ?string $model = User::class;

protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    // Navication Order
    protected static ?int $navigationSort = 1;
    protected static bool $shouldRegisterNavigation = false;

protected static string|UnitEnum|null $navigationGroup = 'Settings';


public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Card Design
                Section::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('email_verified_at'),
                // Password filed required only in the creation time not required in the update time
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->required()
                    ->hiddenOn('edit'),

                    Select::make('role')
                    ->label('User Role')
                    ->options([
                        'admin' => 'Admin',
                        'staff' => 'Staff',
                        'user'  => 'User',
                    ])
                    ->required(),

                // Select::make('roles')
                //     ->multiple()
                //     ->relationship('roles','name') ->preload(),
                    ])->columns(2) // Row(6+6)
                    // Card Design End
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()
                    ->searchable(),
                    // Tables\Columns\TextColumn::make('role')->searchable()
                    // ->searchable(),
                     Tables\Columns\TextColumn::make('role')
                ->label('Role')
                ->searchable()
                ->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                ->dateTime('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')->sortable()
                ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d-m-Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([


                Tables\Actions\ViewAction::make(),
                // ->visible(auth()->user()->can('User Read')),
                Tables\Actions\EditAction::make(),
                // ->visible(condition: auth()->user()->can('User Edit')),
                Tables\Actions\DeleteAction::make(),
                // ->visible(condition: condition: auth()->user()->can('User Delete')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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

            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
           return auth()->user()->role === 'admin';

    }

    public static function canEdit($record): bool
{
    if(auth()->user()->role === 'admin') {
        return true;
    }
    if(auth()->user()->role === 'staff' && $record->role !== 'admin') {
        return true;
    }
    return false;
}

}
