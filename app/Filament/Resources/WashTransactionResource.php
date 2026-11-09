<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WashTransactionResource\Pages;
use App\Models\User;
use App\Models\VehicleType;
use App\Models\WasherCommission;
use App\Models\WashingService;
use App\Models\WashTransaction;
use App\Services\TransactionService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class WashTransactionResource extends Resource
{
    protected static ?string $model = WashTransaction::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static \UnitEnum|string|null $navigationGroup = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi Pencucian';

    protected static ?string $pluralModelLabel = 'Transaksi Pencucian';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['washer', 'vehicleType', 'washingService', 'photoBefore', 'photoAfter']);

        /** @var User $user */
        $user = Auth::user();

        // Strict data isolation: Washers can only see their own transactions!
        if ($user && $user->hasRole('washer')) {
            $query->where('washer_id', $user->id);
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user && $user->hasRole('admin');

        return $schema
            ->schema([
                Wizard::make([
                    Step::make('Pilih Kendaraan')
                        ->description('Pilih kategori dan kelas yang cocok dengan kendaraan pelanggan. Periksa layanan, harga, dan komisi sebelum lanjut.')
                        ->extraAttributes(['class' => 'wash-vehicle-step'])
                        ->schema([
                            Forms\Components\Select::make('washer_id')
                                ->label('Pencuci yang bertugas')
                                ->options(fn () => User::role('washer')->where('is_active', true)->pluck('name', 'id'))
                                ->default(fn () => $user && $user->hasRole('washer') ? $user->id : null)
                                ->disabled(! $isAdmin)
                                ->dehydrated()
                                ->required()
                                ->live()
                                ->helperText('Pilih petugas yang menangani pencucian ini.')
                                ->visible($isAdmin),

                            Forms\Components\Radio::make('vehicle_category')
                                ->label('Kategori kendaraan')
                                ->options([
                                    'motor' => 'Motor',
                                    'mobil' => 'Mobil',
                                ])
                                ->descriptions([
                                    'motor' => 'Matic, bebek, atau sport',
                                    'mobil' => 'Mobil kecil, MPV, atau SUV',
                                ])
                                ->columns(2)
                                ->extraAttributes(['class' => 'wash-vehicle-options'])
                                ->columnSpanFull()
                                ->default('motor')
                                ->live()
                                ->afterStateUpdated(function (Set $set): void {
                                    $set('vehicle_type_id', null);
                                    $set('washing_service_id', null);
                                })
                                ->required(),

                            Forms\Components\Radio::make('vehicle_type_id')
                                ->label('Pilih tipe kendaraan')
                                ->options(fn (Get $get): array => VehicleType::query()
                                    ->where('is_active', true)
                                    ->where('category', $get('vehicle_category'))
                                    ->whereHas('services', fn ($query) => $query->where('is_active', true))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->descriptions(fn (Get $get): array => VehicleType::query()
                                    ->where('is_active', true)
                                    ->where('category', $get('vehicle_category'))
                                    ->whereHas('services', fn ($query) => $query->where('is_active', true))
                                    ->with(['services' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function (VehicleType $vehicleType) use ($get): array {
                                        $washerId = $get('washer_id') ?? (Auth::user()?->hasRole('washer') ? Auth::id() : null);
                                        $commissionSetting = $washerId
                                            ? WasherCommission::query()->where('user_id', $washerId)->first()
                                            : null;
                                        $transactionService = app(TransactionService::class);
                                        $serviceDetails = $vehicleType->services
                                            ->map(function (WashingService $service) use ($transactionService, $commissionSetting): string {
                                                $details = [$service->name];

                                                if (filled($service->description)) {
                                                    $details[] = trim($service->description);
                                                }

                                                $details[] = 'Harga Rp '.number_format((float) $service->price, 0, ',', '.')
                                                    .' · Komisi Rp '.number_format(
                                                    $transactionService->calculateCommissionAmount((float) $service->price, $commissionSetting),
                                                    0,
                                                    ',',
                                                    '.',
                                                    );

                                                return implode("\n", $details);
                                            })
                                            ->implode("\n");

                                        $vehicleExamples = filled($vehicleType->examples)
                                            ? 'Contoh model: '.trim($vehicleType->examples)
                                            : 'Cocokkan kelas kendaraan dengan kendaraan pelanggan.';

                                        return [$vehicleType->id => $vehicleExamples."\n".$serviceDetails];
                                    })
                                    ->all())
                                ->columns(2)
                                ->extraAttributes(['class' => 'wash-vehicle-type-options'])
                                ->disabled(fn (Get $get): bool => blank($get('vehicle_category')))
                                ->visible(fn (Get $get): bool => filled($get('vehicle_category')))
                                ->live()
                                ->afterStateUpdated(function (Set $set, $state): void {
                                    $set('washing_service_id', null);

                                    $serviceIds = WashingService::query()
                                        ->where('is_active', true)
                                        ->where('vehicle_type_id', $state)
                                        ->pluck('id');

                                    if ($serviceIds->count() === 1) {
                                        $set('washing_service_id', $serviceIds->first());
                                    }
                                })
                                ->helperText('Cocokkan kelas dan contoh model pada kartu dengan kendaraan pelanggan.')
                                ->required()
                                ->columnSpanFull(),

                            Forms\Components\Radio::make('washing_service_id')
                                ->label('Pilih paket layanan')
                                ->options(fn (Get $get): array => WashingService::query()
                                    ->where('is_active', true)
                                    ->where('vehicle_type_id', $get('vehicle_type_id'))
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->descriptions(fn (Get $get): array => WashingService::query()
                                    ->where('is_active', true)
                                    ->where('vehicle_type_id', $get('vehicle_type_id'))
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(function (WashingService $service) use ($get): array {
                                        $washerId = $get('washer_id') ?? (Auth::user()?->hasRole('washer') ? Auth::id() : null);
                                        $commissionSetting = $washerId
                                            ? WasherCommission::query()->where('user_id', $washerId)->first()
                                            : null;

                                        $priceAndCommission = 'Harga Rp '.number_format((float) $service->price, 0, ',', '.')
                                                .' · Komisi Rp '.number_format(
                                                    app(TransactionService::class)->calculateCommissionAmount((float) $service->price, $commissionSetting),
                                                    0,
                                                    ',',
                                                    '.',
                                                );

                                        return [
                                            $service->id => filled($service->description)
                                                ? trim($service->description)."\n".$priceAndCommission
                                                : $priceAndCommission,
                                        ];
                                    })
                                    ->all())
                                ->columns(2)
                                ->extraAttributes(['class' => 'wash-service-options'])
                                ->dehydrated()
                                ->helperText('Pilih paket yang diminta pelanggan. Harga dan komisi tercantum di bawah nama paket.')
                                ->visible(fn (Get $get): bool => filled($get('vehicle_type_id')) && WashingService::query()
                                    ->where('is_active', true)
                                    ->where('vehicle_type_id', $get('vehicle_type_id'))
                                    ->count() > 1)
                                ->required(fn (Get $get): bool => filled($get('vehicle_type_id')) && WashingService::query()
                                    ->where('is_active', true)
                                    ->where('vehicle_type_id', $get('vehicle_type_id'))
                                    ->count() > 1)
                                ->columnSpanFull(),

                        ])
                        ->columns(2),

                    Step::make('Foto & Simpan')
                        ->description('Kamera terbuka otomatis. Ambil foto, tunggu unggahan selesai, lalu simpan transaksi.')
                        ->schema([
                            Forms\Components\ViewField::make('photo_after_capture')
                                ->label('Foto setelah dicuci')
                                ->view('filament.forms.components.camera-capture')
                                ->required()
                                ->columnSpanFull(),
                        ]),
                ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button type="submit">
                            Simpan Transaksi
                        </x-filament::button>
                    BLADE)))
                    ->nextAction(fn (Actions\Action $action): Actions\Action => $action->label('Lanjut ke Kamera'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        /** @var User $user */
        $user = Auth::user();
        $isAdmin = $user && $user->hasRole('admin');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_number')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('washer.name')
                    ->label('Pencuci')
                    ->searchable()
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('vehicleType.name')
                    ->label('Kendaraan')
                    ->badge()
                    ->color(fn ($record) => $record->vehicleType?->category === 'motor' ? 'info' : 'warning'),

                Tables\Columns\TextColumn::make('washingService.name')
                    ->label('Layanan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Total Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Komisi Pencuci')
                    ->money('IDR', locale: 'id')
                    ->visible($isAdmin || ($user && $user->hasRole('washer'))),

                Tables\Columns\TextColumn::make('business_revenue')
                    ->label('Pendapatan Usaha')
                    ->money('IDR', locale: 'id')
                    ->visible($isAdmin),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'in_progress' => 'Proses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Batal',
                    }),

                Tables\Columns\ImageColumn::make('photoBefore.photo_path')
                    ->label('Foto Sebelum')
                    ->disk('public')
                    ->square(),

                Tables\Columns\ImageColumn::make('photoAfter.photo_path')
                    ->label('Foto Sesudah')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Transaksi')
                    ->options([
                        'in_progress' => 'Proses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Batal',
                    ]),

                Tables\Filters\SelectFilter::make('washer_id')
                    ->label('Pencuci')
                    ->options(fn () => User::role('washer')->pluck('name', 'id'))
                    ->visible($isAdmin),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\Action::make('complete')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (WashTransaction $record) => $record->status === 'in_progress')
                    ->form([
                        Forms\Components\FileUpload::make('photo_after')
                            ->label('Foto Sesudah Dicuci (Wajib untuk Selesai)')
                            ->image()
                            ->disk('public')
                            ->directory('wash-photos/after')
                            ->maxSize(5120)
                            ->required(),
                    ])
                    ->action(function (WashTransaction $record, array $data) {
                        app(TransactionService::class)->completeTransaction($record, $data['photo_after'] ?? null);
                    }),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()->visible($isAdmin),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWashTransactions::route('/'),
            'create' => Pages\CreateWashTransaction::route('/create'),
            'edit' => Pages\EditWashTransaction::route('/{record}/edit'),
        ];
    }
}
