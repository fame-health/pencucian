<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleTypeResource\Pages;
use App\Models\VehicleType;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleTypeResource extends Resource
{
    protected static ?string $model = VehicleType::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-truck';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Jenis Kendaraan';

    protected static ?string $pluralModelLabel = 'Jenis Kendaraan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Kelas / Tipe Kendaraan')
                    ->placeholder('Contoh: Matic kecil, Matic besar, Mobil MPV')
                    ->helperText('Buat kelas kendaraan yang harganya sama. Contoh: Matic kecil dan Matic besar.')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('examples')
                    ->label('Contoh Model Kendaraan')
                    ->placeholder('Contoh: Beat, Supra')
                    ->helperText('Isi contoh merek atau model agar pencuci mudah mencocokkan kendaraan pelanggan.')
                    ->rows(2)
                    ->columnSpanFull(),
                Forms\Components\Select::make('category')
                    ->label('Kategori Kendaraan')
                    ->options([
                        'motor' => 'Motor',
                        'mobil' => 'Mobil',
                    ])
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
                    ->label('Kelas / Tipe Kendaraan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('examples')
                    ->label('Contoh Model')
                    ->placeholder('-')
                    ->wrap(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'motor' => 'info',
                        'mobil' => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'motor' => 'Motor',
                        'mobil' => 'Mobil',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make()
                    ->failureNotificationTitle('Data gagal dihapus')
                    ->failureNotificationBody('Jenis kendaraan tidak dapat dihapus. Periksa apakah data masih digunakan.'),
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
            'index' => Pages\ListVehicleTypes::route('/'),
            'create' => Pages\CreateVehicleType::route('/create'),
            'edit' => Pages\EditVehicleType::route('/{record}/edit'),
        ];
    }
}
