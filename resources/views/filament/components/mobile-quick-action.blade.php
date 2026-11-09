@if (filament()->auth()->check() && auth()->user()?->can('create', \App\Models\WashTransaction::class) && ! request()->routeIs('filament.admin.resources.wash-transactions.create'))
    <nav class="mobile-wash-action fixed inset-x-0 bottom-0 z-50 flex justify-center border-t border-slate-200 bg-white px-4 py-3 shadow-lg md:hidden" aria-label="Aksi cepat">
        <a
            href="{{ \App\Filament\Resources\WashTransactionResource::getUrl('create') }}"
            wire:navigate
            class="flex h-[3.25rem] w-full max-w-xl items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 text-base font-semibold text-white shadow-md transition active:bg-amber-700"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>Tambah Pencucian</span>
        </a>
    </nav>
@endif
