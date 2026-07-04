<div class="space-y-6">
    <!-- Filter Kontrol Dashboard -->
    <div class="p-4 bg-white rounded-lg shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h3 class="text-lg font-medium text-gray-800">Laporan Analisis Finansial</h3>
            <p class="text-xs text-gray-500">Angka diperbarui secara real-time berdasarkan data impor dan filter parameter waktu Anda.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Pilih Toko</label>
                <select wire:model.live="selectedShop" class="border border-gray-300 rounded-md text-xs px-3 py-1.5 focus:ring-indigo-500">
                    <option value="">-- Semua Toko --</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop }}">{{ $shop }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Rentang Waktu</label>
                <select wire:model.live="timeRange" class="border border-gray-300 rounded-md text-xs px-3 py-1.5 focus:ring-indigo-500">
                    <option value="all">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="7_days">7 Hari Terakhir</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="custom">Pilih Tanggal Kustom 📅</option>
                </select>
            </div>
            @if($timeRange === 'custom')
                <div class="flex items-center gap-2 animate-fadeIn">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Mulai</label>
                        <input type="date" wire:model.live="startDate" class="border border-gray-300 rounded-md text-xs px-2 py-1">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Selesai</label>
                        <input type="date" wire:model.live="endDate" class="border border-gray-300 rounded-md text-xs px-2 py-1">
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if (session('success_target'))
        <div class="px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm font-medium">
            {{ session('success_target') }}
        </div>
    @endif

    <!-- Target Bulanan & Report -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 p-4 bg-white rounded-lg shadow-sm border border-sky-100">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase text-sky-600 tracking-wider">Target Bulanan</p>
                    <h4 class="text-base font-semibold text-gray-800 mt-1">
                        {{ $selectedShop ?: 'Semua Toko' }} - {{ $targetMonthLabel }}
                    </h4>
                    <p class="text-xs text-gray-500 mt-1">Progress dihitung dari omset non-cancelled pada bulan target.</p>
                </div>
                <div class="text-left md:text-right">
                    <p class="text-xs text-gray-400 font-medium">Tercapai</p>
                    <p class="text-2xl font-bold text-sky-700">{{ number_format($targetProgress, 1) }}%</p>
                </div>
            </div>

            <div class="mt-4">
                <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-sky-600 rounded-full transition-all duration-300" style="width: {{ $targetProgress }}%"></div>
                </div>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                    <div class="bg-gray-50 rounded-md px-3 py-2">
                        <span class="block text-gray-400 font-bold uppercase">Omset Bulan Ini</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($monthlySales, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-gray-50 rounded-md px-3 py-2">
                        <span class="block text-gray-400 font-bold uppercase">Target</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($monthlyTarget, 0, ',', '.') }}</span>
                    </div>
                    <div class="bg-gray-50 rounded-md px-3 py-2">
                        <span class="block text-gray-400 font-bold uppercase">Sisa</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($targetRemaining, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if($isOwner)
                <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-end gap-3">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Bulan Target</label>
                        <input type="month" wire:model.live="targetMonth" class="border border-gray-300 rounded-md text-xs px-3 py-1.5 focus:ring-sky-500">
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Target Toko Terpilih</label>
                        <input type="number" min="0" step="1000" wire:model="targetAmount" @disabled(!$selectedShop) class="w-full border border-gray-300 rounded-md text-xs px-3 py-1.5 focus:ring-sky-500 disabled:bg-gray-100" placeholder="Pilih toko lalu isi target">
                        <x-input-error :messages="$errors->get('targetAmount')" class="mt-1" />
                    </div>
                    <button wire:click="saveMonthlyTarget" @disabled(!$selectedShop) class="px-4 py-2 bg-sky-600 hover:bg-sky-700 disabled:bg-gray-300 text-white rounded-md text-xs font-bold transition">
                        Simpan Target
                    </button>
                </div>
            @endif
        </div>

        <div class="p-4 bg-white rounded-lg shadow-sm border border-gray-200 flex flex-col justify-between">
            <div>
                <p class="text-xs font-bold uppercase text-gray-500 tracking-wider">Investor / Owner Report</p>
                <h4 class="text-base font-semibold text-gray-800 mt-1">Printable Analytics</h4>
                <p class="text-xs text-gray-500 mt-1">Berisi KPI, chart performa SKU, target bulanan, dan tabel ringkasan sesuai filter aktif.</p>
            </div>
            <a href="{{ route('analytics.report', ['shop' => $selectedShop, 'range' => $timeRange, 'start' => $startDate, 'end' => $endDate, 'target_month' => $targetMonth]) }}"
               target="_blank"
               class="mt-4 inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-900 hover:bg-gray-800 text-white rounded-md text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8V4h10v4m-9 8H6a2 2 0 01-2-2v-4a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2h-2m-8 0h8v4H8v-4z"/></svg>
                Export PDF / Print
            </a>
        </div>
    </div>

    <!-- Tombol Toggle MoM Comparison -->
    <div class="flex justify-end mb-2">
        <button wire:click="toggleComparison" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border transition-all duration-200 {{ $showComparison ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400 hover:text-indigo-600' }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            {{ $showComparison ? 'Sembunyikan Perbandingan' : 'Bandingkan dengan Periode Lalu' }}
        </button>
    </div>

    <!-- Grid Kartu KPI -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Omset Kotor</p>
            <p class="text-xl font-bold text-gray-800 mt-1">Rp {{ number_format($omsetKotor, 0, ',', '.') }}</p>
            @if($showComparison && $comparisonData)
                <div class="flex items-center gap-1 mt-1 {{ $comparisonData['omset_delta'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M{{ $comparisonData['omset_delta'] >= 0 ? '5 10l7-7m0 0l7 7m-7-7v18' : '19 14l-7 7m0 0l-7-7m7 7V3' }}"/></svg>
                    <span class="text-[10px] font-bold">{{ number_format(abs($comparisonData['omset_delta']), 1) }}%</span>
                    <span class="text-[10px] text-gray-400">vs {{ $comparisonData['period_label'] }}</span>
                </div>
            @endif
        </div>
        <div class="p-4 bg-white border border-emerald-100 rounded-lg shadow-sm bg-gradient-to-br from-white to-emerald-50">
            <p class="text-xs font-medium text-emerald-600 uppercase tracking-wider">Dana Cair (TikTok)</p>
            <p class="text-xl font-bold text-emerald-700 mt-1">Rp {{ number_format($cairBersih, 0, ',', '.') }}</p>
            @if($showComparison && $comparisonData)
                <div class="flex items-center gap-1 mt-1 {{ $comparisonData['cair_delta'] >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M{{ $comparisonData['cair_delta'] >= 0 ? '5 10l7-7m0 0l7 7m-7-7v18' : '19 14l-7 7m0 0l-7-7m7 7V3' }}"/></svg>
                    <span class="text-[10px] font-bold">{{ number_format(abs($comparisonData['cair_delta']), 1) }}%</span>
                    <span class="text-[10px] text-gray-400">vs {{ $comparisonData['period_label'] }}</span>
                </div>
            @endif
        </div>
        <div class="p-4 bg-white border border-amber-200 rounded-lg shadow-sm bg-gradient-to-br from-white to-amber-50">
            <p class="text-xs font-medium text-amber-700 uppercase tracking-wider">⚠️ Belum Cair</p>
            <p class="text-xl font-bold text-amber-800 mt-1">Rp {{ number_format($totalDanaMenggantung, 0, ',', '.') }}</p>
            <span class="text-[10px] text-amber-600 font-medium">Uang tertahan di TikTok</span>
        </div>
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">HPP & Overhead</p>
            <p class="text-xl font-bold text-gray-700 mt-1">Rp {{ number_format($hppOverhead, 0, ',', '.') }}</p>
        </div>
        <div class="p-4 bg-indigo-900 rounded-lg shadow-sm text-white">
            <p class="text-xs font-medium text-indigo-200 uppercase tracking-wider">Net Profit Bersih</p>
            <p class="text-xl font-bold mt-1">Rp {{ number_format($profitBersih, 0, ',', '.') }}</p>
        </div>
    </div>

    @if($showComparison && $comparisonData)
        @php
            $maxOmsetCompare = max($omsetKotor, $comparisonData['omset'], 1);
            $maxProfitCompare = max(abs($profitBersih), abs($comparisonData['profit']), 1);
            $maxOrderCompare = max($comparisonData['current_order_count'], $comparisonData['order_count'], 1);
            $comparisonRows = [
                [
                    'label' => 'Omset',
                    'current' => $omsetKotor,
                    'previous' => $comparisonData['omset'],
                    'delta' => $comparisonData['omset_delta'],
                    'max' => $maxOmsetCompare,
                    'format' => 'currency',
                ],
                [
                    'label' => 'Profit',
                    'current' => $profitBersih,
                    'previous' => $comparisonData['profit'],
                    'delta' => $comparisonData['profit_delta'],
                    'max' => $maxProfitCompare,
                    'format' => 'currency',
                ],
                [
                    'label' => 'Order Count',
                    'current' => $comparisonData['current_order_count'],
                    'previous' => $comparisonData['order_count'],
                    'delta' => $comparisonData['order_delta'],
                    'max' => $maxOrderCompare,
                    'format' => 'number',
                ],
            ];
        @endphp
        <div class="p-5 bg-white rounded-lg shadow-sm border border-indigo-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
                <div>
                    <h4 class="font-semibold text-gray-800 text-sm">Bulan/Periode Ini vs Periode Lalu</h4>
                    <p class="text-[11px] text-gray-500">Pembanding: {{ $comparisonData['period_label'] }}</p>
                </div>
                <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full">Omset, Profit, Order Count</span>
            </div>
            <div class="space-y-4">
                @foreach($comparisonRows as $row)
                    @php
                        $currentWidth = min((abs($row['current']) / $row['max']) * 100, 100);
                        $previousWidth = min((abs($row['previous']) / $row['max']) * 100, 100);
                        $deltaColor = $row['delta'] >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50';
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-[130px,1fr,90px] gap-3 items-center">
                        <div>
                            <p class="text-xs font-bold text-gray-700">{{ $row['label'] }}</p>
                            <span class="inline-flex mt-1 px-2 py-0.5 rounded text-[10px] font-bold {{ $deltaColor }}">
                                {{ $row['delta'] >= 0 ? 'Naik' : 'Turun' }} {{ number_format(abs($row['delta']), 1) }}%
                            </span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="w-16 text-[10px] text-gray-400 font-bold uppercase">Sekarang</span>
                                <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-600 rounded-full" style="width: {{ $currentWidth }}%"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-16 text-[10px] text-gray-400 font-bold uppercase">Lalu</span>
                                <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-gray-400 rounded-full" style="width: {{ $previousWidth }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right text-[11px] text-gray-600">
                            <p class="font-bold text-gray-800">
                                @if($row['format'] === 'currency')
                                    Rp {{ number_format($row['current'], 0, ',', '.') }}
                                @else
                                    {{ number_format($row['current'], 0, ',', '.') }}
                                @endif
                            </p>
                            <p>
                                @if($row['format'] === 'currency')
                                    Rp {{ number_format($row['previous'], 0, ',', '.') }}
                                @else
                                    {{ number_format($row['previous'], 0, ',', '.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- TRENDING SKU -->
    <div class="p-5 bg-white rounded-lg shadow-sm border border-indigo-100 flex flex-col mb-6">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-indigo-600 rounded-full"></span>
                    📊 Produk Trending (per SKU)
                </h4>
                <p class="text-[11px] text-gray-500">Produk paling banyak terjual sesuai filter toko dan waktu.</p>
            </div>
            <span class="text-xs font-medium bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded">{{ $skuTrendingList->count() }} SKU</span>
        </div>
        <div class="overflow-x-auto overflow-y-auto max-h-80 border border-gray-100 rounded">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-indigo-50 text-indigo-800 font-semibold uppercase sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-2 text-center w-12">No</th>
                        <th class="px-3 py-2 text-left">SKU ID</th>
                        <th class="px-3 py-2 text-left">Nama Produk</th>
                        <th class="px-3 py-2 text-center w-24">Terjual</th>
                        <th class="px-3 py-2 text-right">Omset</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-600">
                    @forelse($skuTrendingList as $sku)
                        <tr class="hover:bg-indigo-50/20 transition">
                            <td class="px-4 py-2.5 text-center font-medium text-gray-400 bg-gray-50/30">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2.5 font-mono font-semibold text-indigo-600">{{ $sku->sku_id ?? 'N/A' }}</td>
                            <td class="px-3 py-2.5 truncate max-w-xs text-gray-700">{{ $sku->nama_produk }}</td>
                            <td class="px-3 py-2.5 text-center font-bold text-gray-900">{{ number_format($sku->total_terjual, 0, ',', '.') }} pcs</td>
                            <td class="px-3 py-2.5 text-right font-medium text-gray-800">Rp {{ number_format($sku->total_omset, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-4 text-center text-gray-400 text-[11px]">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PROFIT BREAKDOWN -->
    <div class="p-5 bg-white rounded-lg shadow-sm border border-emerald-100 flex flex-col mb-6">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-emerald-600 rounded-full"></span>
                    💰 Profit Breakdown per SKU
                </h4>
                <p class="text-[11px] text-gray-500">Keuntungan bersih per produk.</p>
            </div>
            <span class="text-xs font-medium bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded">{{ $skuProfitList->count() }} SKU</span>
        </div>
        <div class="overflow-x-auto overflow-y-auto max-h-80 border border-gray-100 rounded">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-emerald-50 text-emerald-800 font-semibold uppercase sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-center w-10">No</th>
                        <th class="px-3 py-2 text-left">SKU ID</th>
                        <th class="px-3 py-2 text-left">Nama Produk</th>
                        <th class="px-3 py-2 text-center">Terjual</th>
                        <th class="px-3 py-2 text-right">Omset</th>
                        <th class="px-3 py-2 text-right">HPP</th>
                        <th class="px-3 py-2 text-right">Overhead</th>
                        <th class="px-3 py-2 text-right">Dana Cair</th>
                        <th class="px-3 py-2 text-right">Profit</th>
                        <th class="px-3 py-2 text-center">Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-600">
                    @forelse($skuProfitList as $sku)
                        @php
                            $margin = $sku['total_cair'] > 0 ? ($sku['total_profit'] / $sku['total_cair']) * 100 : 0;
                            $rowClass = $sku['total_profit'] >= 0 ? 'hover:bg-emerald-50/20' : 'hover:bg-red-50/30 bg-red-50/10';
                            $profitColor = $sku['total_profit'] >= 0 ? 'text-emerald-700' : 'text-red-600';
                            $marginBadge = $margin >= 20 ? 'bg-emerald-100 text-emerald-800' : ($margin >= 0 ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800');
                        @endphp
                        <tr class="{{ $rowClass }} transition">
                            <td class="px-3 py-2.5 text-center font-medium text-gray-400">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2.5 font-mono font-semibold text-indigo-600 text-[10px]">{{ $sku['sku_id'] ?? 'N/A' }}</td>
                            <td class="px-3 py-2.5 truncate max-w-xs text-gray-700">{{ $sku['product_name'] }}</td>
                            <td class="px-3 py-2.5 text-center font-bold">{{ number_format($sku['total_sold'], 0, ',', '.') }} pcs</td>
                            <td class="px-3 py-2.5 text-right font-medium">Rp {{ number_format($sku['total_omset'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-500">Rp {{ number_format($sku['total_hpp'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-500">Rp {{ number_format($sku['total_overhead'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right font-medium">Rp {{ number_format($sku['total_cair'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right font-bold {{ $profitColor }}">Rp {{ number_format($sku['total_profit'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-center"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $marginBadge }}">{{ number_format($margin, 1) }}%</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-3 py-4 text-center text-gray-400 text-[11px]">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- RETURN & REFUND TRACKER -->
    <div class="p-5 bg-white rounded-lg shadow-sm border border-rose-100 flex flex-col mb-6">
        <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-rose-600 rounded-full"></span>
                    Return & Refund Tracker
                </h4>
                <p class="text-[11px] text-gray-500">Indikasi retur/refund dari order yang belum cair terlalu lama.</p>
            </div>
            <div class="flex gap-2">
                <span class="text-xs font-medium bg-rose-50 text-rose-700 px-2.5 py-1 rounded">{{ $returnRefundCandidates->count() }} Kandidat</span>
                <span class="text-xs font-medium bg-red-50 text-red-700 px-2.5 py-1 rounded">{{ $returnRefundHighRisk }} Risiko Tinggi</span>
                <span class="text-xs font-medium bg-gray-50 text-gray-700 px-2.5 py-1 rounded">Rp {{ number_format($returnRefundAmount, 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto max-h-80 border border-gray-100 rounded">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-rose-50 text-rose-800 font-semibold uppercase sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-center w-10">No</th>
                        <th class="px-3 py-2 text-left">ID Pesanan</th>
                        <th class="px-3 py-2 text-left">Produk</th>
                        <th class="px-3 py-2 text-center">Umur Belum Cair</th>
                        <th class="px-3 py-2 text-right">Nilai</th>
                        <th class="px-3 py-2 text-center">Status Audit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-600">
                    @forelse($returnRefundCandidates as $candidate)
                        @php
                            $riskBadge = $candidate->refund_risk === 'tinggi'
                                ? 'bg-red-100 text-red-700'
                                : 'bg-amber-100 text-amber-700';
                            $riskLabel = $candidate->refund_risk === 'tinggi' ? 'Indikasi Return/Refund' : 'Pantau Pencairan';
                        @endphp
                        <tr class="{{ $candidate->refund_risk === 'tinggi' ? 'bg-red-50/20 hover:bg-red-50/40' : 'hover:bg-amber-50/30' }} transition">
                            <td class="px-3 py-2.5 text-center text-gray-400">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2.5 font-mono text-indigo-600">{{ $candidate->order_id }}</td>
                            <td class="px-3 py-2.5 truncate max-w-xs">{{ $candidate->product_name }}</td>
                            <td class="px-3 py-2.5 text-center font-bold">{{ $candidate->days_pending }} hari</td>
                            <td class="px-3 py-2.5 text-right font-medium">Rp {{ number_format($candidate->order_amount, 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-center"><span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $riskBadge }}">{{ $riskLabel }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-4 text-center text-gray-400 text-[11px]">Tidak ada order lama yang belum cair.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- PESANAN BELUM CAIR -->
        <div class="p-5 bg-white rounded-lg shadow-sm border border-amber-200 flex flex-col h-[400px]">
            <div class="mb-4">
                <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-amber-500 rounded-full"></span>
                    Audit: Pesanan Menggantung
                </h4>
                <p class="text-[11px] text-gray-500">Order belum dilepas ke laporan Income.</p>
            </div>
            <div class="overflow-x-auto overflow-y-auto flex-grow max-h-64 border border-gray-100 rounded">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-amber-50 text-amber-800 font-semibold uppercase sticky top-0 z-10">
                        <tr>
                            <th class="px-3 py-2 text-center w-10">No</th>
                            <th class="px-3 py-2 text-left">ID Pesanan</th>
                            <th class="px-3 py-2">Tanggal</th>
                            <th class="px-3 py-2 text-right">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse($pesananBelumCairList as $pbc)
                            <tr class="hover:bg-amber-50/30">
                                <td class="px-3 py-2.5 text-center text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-3 py-2.5 font-mono text-indigo-600">{{ $pbc->order_id }}</td>
                                <td class="px-3 py-2.5">{{ \Carbon\Carbon::parse($pbc->created_time)->format('d M Y') }}</td>
                                <td class="px-3 py-2.5 text-right font-medium">Rp {{ number_format($pbc->order_amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400 text-[11px]">Semua dana sudah cair!</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ANOMALI ONGKIR + CLAIM TRACKER -->
        <div class="p-5 bg-white rounded-lg shadow-sm border border-red-100 flex flex-col h-[400px]">
            <div class="mb-3">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-ping"></span>
                        Klaim Ongkir
                    </h4>
                    <span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold">Potensi: Rp {{ number_format($totalPotensiKlaim, 0, ',', '.') }}</span>
                </div>
                <p class="text-[11px] text-gray-500">Klik baris untuk update status klaim.</p>
            </div>
            <div class="flex gap-2 mb-3">
                <div class="flex-1 bg-gray-50 rounded px-2 py-1.5 text-center">
                    <p class="text-[9px] text-gray-400 font-bold uppercase">Belum</p>
                    <p class="text-xs font-bold text-gray-600">{{ collect($anomaliOngkirList)->filter(fn($a) => !in_array(trim($a->order_id), $claimOrderIds))->count() }}</p>
                </div>
                <div class="flex-1 bg-blue-50 rounded px-2 py-1.5 text-center">
                    <p class="text-[9px] text-blue-400 font-bold uppercase">Proses</p>
                    <p class="text-xs font-bold text-blue-600">{{ collect($claimsMap)->where('status', 'proses_klaim')->count() }}</p>
                </div>
                <div class="flex-1 bg-emerald-50 rounded px-2 py-1.5 text-center">
                    <p class="text-[9px] text-emerald-400 font-bold uppercase">Berhasil</p>
                    <p class="text-xs font-bold text-emerald-600">{{ collect($claimsMap)->where('status', 'berhasil')->count() }}</p>
                </div>
                <div class="flex-1 bg-amber-50 rounded px-2 py-1.5 text-center">
                    <p class="text-[9px] text-amber-400 font-bold uppercase">Diklaim</p>
                    <p class="text-xs font-bold text-amber-600">Rp {{ number_format($totalSudahDiklaim, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="overflow-x-auto overflow-y-auto flex-grow max-h-52 border border-gray-100 rounded">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-red-50 text-red-800 font-semibold uppercase sticky top-0 z-10">
                        <tr>
                            <th class="px-2 py-2 text-left">ID</th>
                            <th class="px-2 py-2 text-right">Kerugian</th>
                            <th class="px-2 py-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse($anomaliOngkirList as $anomali)
                            @php
                                $tid = trim($anomali->order_id);
                                $hasClaim = in_array($tid, $claimOrderIds);
                                $claim = $hasClaim ? $claimsMap->get($tid) : null;
                                $cst = $claim ? $claim->status : 'belum_diklaim';
                                $rc = $cst === 'berhasil' ? 'bg-emerald-50/40' : ($cst === 'ditolak' ? 'bg-gray-50/40' : ($anomali->rasio_bengkak >= 2 ? 'bg-red-50' : 'bg-amber-50/60'));
                                $sb = match($cst) { 'berhasil' => 'bg-emerald-100 text-emerald-700', 'proses_klaim' => 'bg-blue-100 text-blue-700', 'ditolak' => 'bg-gray-100 text-gray-600', default => 'bg-red-100 text-red-600' };
                                $sl = match($cst) { 'berhasil' => 'Berhasil', 'proses_klaim' => 'Proses', 'ditolak' => 'Ditolak', default => 'Belum' };
                            @endphp
                            <tr class="{{ $rc }} transition {{ $isOwner ? 'cursor-pointer' : '' }}" @if($isOwner) wire:click="openClaimModal('{{ $anomali->order_id }}', '{{ $anomali->tracking_id }}', {{ $anomali->selisih_rugi }})" @endif>
                                <td class="px-2 py-2 font-mono text-[10px]">{{ $anomali->order_id }}</td>
                                <td class="px-2 py-2 text-right font-bold text-red-600">-Rp {{ number_format($anomali->selisih_rugi, 0, ',', '.') }}</td>
                                <td class="px-2 py-2 text-center"><span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $sb }}">{{ $sl }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-3 py-4 text-center text-gray-400 text-[11px]">Aman! Tidak ada kebocoran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- REGIONAL DASHBOARD -->
    <div class="p-5 bg-white rounded-lg shadow-sm border border-blue-100 flex flex-col mb-6 mt-6">
        <div class="mb-4 flex justify-between items-center">
            <div>
                <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-blue-600 rounded-full"></span>
                    🗺️ Regional Dashboard
                </h4>
                <p class="text-[11px] text-gray-500">Performa per Provinsi.</p>
            </div>
            <span class="text-xs font-medium bg-blue-50 text-blue-700 px-2.5 py-1 rounded">{{ $provinceList->count() }} Provinsi</span>
        </div>
        <div class="overflow-x-auto overflow-y-auto max-h-80 border border-gray-100 rounded">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-blue-50 text-blue-800 font-semibold uppercase sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-2 text-center w-10">No</th>
                        <th class="px-3 py-2 text-left">Provinsi</th>
                        <th class="px-3 py-2 text-center">Order</th>
                        <th class="px-3 py-2 text-right">Omset</th>
                        <th class="px-3 py-2 text-right">Ongkir Rata</th>
                        <th class="px-3 py-2 text-center">Menggantung</th>
                        <th class="px-3 py-2 text-right">Nilai Menggantung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-600">
                    @forelse($provinceList as $prov)
                        <tr class="hover:bg-blue-50/20 transition">
                            <td class="px-3 py-2.5 text-center text-gray-400">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2.5 font-medium text-gray-800">{{ $prov['province'] }}</td>
                            <td class="px-3 py-2.5 text-center font-bold">{{ number_format($prov['total_orders'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right font-medium">Rp {{ number_format($prov['total_omset'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-500">Rp {{ number_format($prov['avg_shipping'], 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-center">@if($prov['pending_orders'] > 0)<span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full">{{ $prov['pending_orders'] }}</span>@else<span class="text-gray-300">-</span>@endif</td>
                            <td class="px-3 py-2.5 text-right font-bold {{ $prov['pending_amount'] > 0 ? 'text-amber-700' : 'text-gray-400' }}">Rp {{ number_format($prov['pending_amount'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-4 text-center text-gray-400 text-[11px]">Tidak ada data regional.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============ MODAL SHIPPING CLAIM ============ -->
    @if($showClaimModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeClaimModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-gradient-to-r from-red-500 to-rose-600 px-4 py-3 sm:px-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Update Status Klaim Ongkir
                        </h3>
                        <button wire:click="closeClaimModal" class="text-white hover:text-red-100 transition"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <p class="mt-1 text-xs text-red-100 font-medium">Order ID: <span class="font-mono">{{ $claimOrderId }}</span> | Kerugian: <span class="font-bold">Rp {{ number_format($claimSelisih, 0, ',', '.') }}</span></p>
                </div>
                <div class="px-4 py-4 sm:px-6">
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <label class="col-span-2 flex items-center gap-2 p-3 rounded-lg bg-red-50 border border-red-100 cursor-pointer">
                                <input type="checkbox" wire:model.live="claimSudahDiklaim" class="rounded border-red-300 text-red-600 focus:ring-red-500">
                                <span class="text-sm font-bold text-red-700">Sudah diklaim ke ekspedisi</span>
                            </label>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Ekspedisi</label>
                                <select wire:model="claimEkspedisi" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-red-500">
                                    <option value="">-- Pilih --</option>
                                    <option value="J&T">J&T Express</option>
                                    <option value="JNE">JNE</option>
                                    <option value="SiCepat">SiCepat</option>
                                    <option value="Ninja">Ninja Xpress</option>
                                    <option value="AnterAja">AnterAja</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Status Klaim</label>
                                <select wire:model="claimStatus" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-red-500">
                                    <option value="belum_diklaim">🔴 Belum Diklaim</option>
                                    <option value="proses_klaim">🔵 Proses Klaim</option>
                                    <option value="berhasil">🟢 Berhasil</option>
                                    <option value="ditolak">⚫ Ditolak</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Tiket</label>
                                <input type="text" wire:model="claimTicketNumber" placeholder="TKT-12345" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-red-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Klaim</label>
                                <input type="date" wire:model="claimTanggalKlaim" class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-red-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Keterangan</label>
                            <textarea wire:model="claimKeterangan" rows="2" placeholder="Catatan tambahan..." class="w-full border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-red-500"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end gap-2">
                    <button wire:click="closeClaimModal" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm font-bold transition">Batal</button>
                    <button wire:click="saveClaim" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-bold shadow-md transition">Simpan Klaim</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
