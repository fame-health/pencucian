<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WasherResource\Pages;
use App\Models\User;
use App\Models\WasherCommission;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class WasherResource extends Resource
{
    protected static ?string $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $modelLabel = 'Pencuci';

    protected static ?string $pluralModelLabel = 'Manajemen Pencuci';

    public static function getEloquentQuery(): Builder
    {
        // Restrict list to users with role 'washer' (or all non-admin users)
        return parent::getEloquentQuery()->with('commissionSetting')->whereHas('roles', function ($q) {
            $q->where('name', 'washer');
        });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Akun Pencuci')
                    ->description('Kelola identitas, kontak, dan status akun pencuci.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email Google')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('ktp_photo')
                            ->label('Foto KTP')
                            ->helperText('Unggah foto KTP yang jelas. File disimpan secara privat dan tidak digunakan sebagai foto profil.')
                            ->image()
                            ->disk('private')
                            ->directory('washer-ktp')
                            ->visibility('private')
                            ->maxSize(5120),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Akun pendaftar Google menunggu persetujuan. Tetapkan komisi sebelum mengaktifkan agar transaksi memakai komisi yang benar.')
                            ->live()
                            ->default(true)
                            ->required(),
                    ])->columns(2),

                Section::make('Pengaturan Komisi')
                    ->description('Komisi dihitung untuk setiap transaksi pencucian yang ditangani pencuci ini.')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\Select::make('commission_type')
                            ->label('Cara Menghitung Komisi')
                            ->options([
                                'percentage' => 'Persentase dari harga layanan',
                                'fixed' => 'Nominal tetap per transaksi',
                            ])
                            ->default(fn (string $operation): ?string => $operation === 'create' ? 'percentage' : null)
                            ->live()
                            ->required(fn (Get $get): bool => (bool) $get('is_active')),
                        Forms\Components\TextInput::make('commission_value')
                            ->label(fn (Get $get): string => $get('commission_type') === 'percentage' ? 'Persentase Komisi' : 'Nominal Komisi')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn (Get $get): ?int => $get('commission_type') === 'percentage' ? 100 : null)
                            ->default(fn (string $operation): ?int => $operation === 'create' ? 30 : null)
                            ->prefix(fn (Get $get) => $get('commission_type') === 'percentage' ? '%' : 'Rp')
                            ->live(debounce: 500)
                            ->required(fn (Get $get): bool => (bool) $get('is_active'))
                            ->helperText(function (Get $get): string {
                                if (blank($get('commission_type')) || blank($get('commission_value'))) {
                                    return 'Komisi belum ditetapkan. Isi nilai komisi sebelum mengaktifkan akun ini.';
                                }

                                $samplePrice = 20000;
                                $value = (float) ($get('commission_value') ?? 30);

                                if ($get('commission_type') === 'fixed') {
                                    $commission = min($samplePrice, $value);

                                    return 'Contoh harga layanan Rp 20.000: pencuci menerima Rp '.number_format($commission, 0, ',', '.')
                                        .' dan usaha menerima Rp '.number_format($samplePrice - $commission, 0, ',', '.')
                                        .'. Komisi tetap tidak bisa melebihi harga layanan.';
                                }

                                $commission = $samplePrice * min(100, max(0, $value)) / 100;

                                return 'Contoh harga layanan Rp 20.000: '.rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',')
                                    .'% × Rp 20.000 = Rp '.number_format($commission, 0, ',', '.')
                                    .' untuk pencuci; sisanya Rp '.number_format($samplePrice - $commission, 0, ',', '.')
                                    .' menjadi pendapatan usaha.';
                            })
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email Google')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('No. Telepon'),
                Tables\Columns\TextColumn::make('commissionSetting.commission_value')
                    ->label('Komisi')
                    ->formatStateUsing(function ($record) {
                        $comm = $record->commissionSetting;
                        if (!$comm) return 'Belum diatur';
                        return $comm->commission_type === 'percentage'
                            ? $comm->commission_value . '%'
                            : 'Rp ' . number_format($comm->commission_value, 0, ',', '.');
                    }),
                Tables\Columns\TextColumn::make('is_active')
                    ->label('Status Aktif')
                    ->badge()
                    ->formatStateUsing(fn (User $record): string => $record->is_active
                        ? 'Aktif'
                        : ($record->commissionSetting ? 'Nonaktif' : 'Menunggu aktivasi'))
                    ->color(fn (User $record): string => $record->is_active
                        ? 'success'
                        : ($record->commissionSetting ? 'gray' : 'warning')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('toggle_active')
                    ->label(fn (User $record) => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
                    ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->disabled(fn (User $record): bool => !$record->is_active && !$record->commissionSetting)
                    ->tooltip(fn (User $record): ?string => !$record->is_active && !$record->commissionSetting
                        ? 'Tetapkan komisi di formulir Ubah sebelum mengaktifkan pencuci.'
                        : null)
                    ->action(function (User $record) {
                        $record->update(['is_active' => !$record->is_active]);
                    }),
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
            'index' => Pages\ListWashers::route('/'),
            'create' => Pages\CreateWasher::route('/create'),
            'edit' => Pages\EditWasher::route('/{record}/edit'),
        ];
    }
}
