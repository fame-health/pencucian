<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WashingServiceResource\Pages;
use App\Models\WashingService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class WashingServiceResource extends Resource
{
    protected static ?string $model = WashingService::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-sparkles';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Layanan Pencucian';

    protected static ?string $pluralModelLabel = 'Layanan & Harga Pencucian';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('vehicle_type_id')
                    ->label('Jenis Kendaraan')
                    ->relationship('vehicleType', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Layanan')
                    ->placeholder('Contoh: Cuci Salju & Wax')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi Layanan')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->label('Harga (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Layanan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicleType.name')
                    ->label('Jenis Kendaraan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vehicleType.category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn ($state) => $state === 'motor' ? 'info' : 'warning')
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vehicle_type_id')
                    ->label('Jenis Kendaraan')
                    ->relationship('vehicleType', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWashingServices::route('/'),
            'create' => Pages\CreateWashingService::route('/create'),
            'edit' => Pages\EditWashingService::route('/{record}/edit'),
        ];
    }
}
