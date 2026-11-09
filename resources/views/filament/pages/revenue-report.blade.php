<x-filament-panels::page>
    <div class="mb-6">
        <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            {{ $this->form }}
        </div>
    </div>

    @php
        $summary = $this->reportSummary;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Transaksi</span>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($summary['total_count']) }}</div>
            <span class="text-xs text-gray-400">Pekerjaan Selesai</span>
        </div>

        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Omzet Gross</span>
            <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}</div>
            <span class="text-xs text-gray-400">Sebelum dipotong komisi</span>
        </div>

        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Komisi Pencuci</span>
            <div class="text-2xl font-bold text-warning-600 dark:text-warning-400 mt-1">Rp {{ number_format($summary['total_commission'], 0, ',', '.') }}</div>
            <span class="text-xs text-gray-400">Hak Pencuci</span>
        </div>

        <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan Bersih Usaha</span>
            <div class="text-2xl font-bold text-success-600 dark:text-success-400 mt-1">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
            <span class="text-xs text-gray-400">Laba Bersih Tempat Pencucian</span>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 font-bold text-gray-900 dark:text-white">
            Rincian Pendapatan per Pencuci (Washer)
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700/50 uppercase text-xs text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Nama Pencuci</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3 text-center">Jumlah Cucian</th>
                        <th class="px-4 py-3 text-right">Total Omzet</th>
                        <th class="px-4 py-3 text-right">Total Komisi (Hak Pencuci)</th>
                        <th class="px-4 py-3 text-right">Pendapatan Usaha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->washerBreakdown as $washer)
                        @php
                            $omzet = (float) ($washer->total_omzet ?? 0);
                            $comm = (float) ($washer->total_commission ?? 0);
                            $net = $omzet - $comm;
                        @endphp
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">{{ $washer->name }}</td>
                            <td class="px-4 py-3">{{ $washer->email }}</td>
                            <td class="px-4 py-3 text-center font-medium">{{ number_format($washer->total_washes) }}</td>
                            <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($omzet, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-warning-600 dark:text-warning-400">Rp {{ number_format($comm, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-success-600 dark:text-success-400">Rp {{ number_format($net, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-400">Tidak ada data pencuci.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
