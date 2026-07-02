<div class="space-y-6">
    <!-- Filter Kontrol Dashboard -->
    <div class="p-4 bg-white rounded-lg shadow-sm flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h3 class="text-lg font-medium text-gray-800">Laporan Analisis Finansial</h3>
            <p class="text-xs text-gray-500">Angka diperbarui secara real-time berdasarkan data impor dan filter parameter waktu Anda.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <!-- Filter Toko -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Pilih Toko</label>
                <select wire:model.live="selectedShop" class="border border-gray-300 rounded-md text-xs px-3 py-1.5 focus:ring-indigo-500">
                    <option value="">-- Semua Toko --</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop }}">{{ $shop }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Rentang Waktu -->
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

            <!-- Input Tanggal Dinamis (Hanya muncul jika memilih 'custom') -->
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

    <!-- Grid Kartu KPI Keuangan (Kini Jadi 5 Kartu) -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">Omset Kotor</p>
            <p class="text-xl font-bold text-gray-800 mt-1">Rp {{ number_format($omsetKotor, 0, ',', '.') }}</p>
        </div>

        <div class="p-4 bg-white border border-emerald-100 rounded-lg shadow-sm bg-gradient-to-br from-white to-emerald-50">
            <p class="text-xs font-medium text-emerald-600 uppercase tracking-wider">Dana Cair (TikTok)</p>
            <p class="text-xl font-bold text-emerald-700 mt-1">Rp {{ number_format($cairBersih, 0, ',', '.') }}</p>
        </div>

        <!-- NEW CARD: DANA MENGGANTUNG -->
        <div class="p-4 bg-white border border-amber-200 rounded-lg shadow-sm bg-gradient-to-br from-white to-amber-50">
            <p class="text-xs font-medium text-amber-700 uppercase tracking-wider">⚠️ Belum Cair (Unmatched)</p>
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

    <!-- KOTAK LIST PRODUK TRENDING BERDASARKAN SKU -->
<div class="p-5 bg-white rounded-lg shadow-sm border border-indigo-100 flex flex-col mb-6">
    <div class="mb-4 flex justify-between items-center">
        <div>
            <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-indigo-600 rounded-full"></span>
                📊 Performa Penjualan & Produk Trending (per SKU)
            </h4>
            <p class="text-[11px] text-gray-500">Daftar produk yang paling banyak terjual (dalam pcs) sesuai filter toko dan waktu.</p>
        </div>
        <span class="text-xs font-medium bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded">
            {{ $skuTrendingList->count() }} SKU Aktif
        </span>
    </div>

    <!-- Kontainer Tabel Ber-scroll Vertikal -->
    <div class="overflow-x-auto overflow-y-auto max-h-80 border border-gray-100 rounded">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
            <thead class="bg-indigo-50 text-indigo-800 font-semibold uppercase sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-2 text-center bg-indigo-50 w-12">No</th>
                    <th class="px-3 py-2 text-left bg-indigo-50">SKU ID</th>
                    <th class="px-3 py-2 text-left bg-indigo-50">Nama Produk Sampel</th>
                    <th class="px-3 py-2 text-center bg-indigo-50 w-24">Terjual (Pcs)</th>
                    <th class="px-3 py-2 text-right bg-indigo-50">Kontribusi Omset</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-600">
                @forelse($skuTrendingList as $sku)
                    <tr class="hover:bg-indigo-50/20 transition">
                        <td class="px-4 py-2.5 text-center font-medium text-gray-400 bg-gray-50/30">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2.5 font-mono font-semibold text-indigo-600">{{ $sku->sku_id ?? 'N/A' }}</td>
                        <td class="px-3 py-2.5 truncate max-w-xs text-gray-700" title="{{ $sku->nama_produk }}">{{ $sku->nama_produk }}</td>
                        <td class="px-3 py-2.5 text-center font-bold text-gray-900 bg-indigo-50/10">{{ number_format($sku->total_terjual, 0, ',', '.') }} pcs</td>
                        <td class="px-3 py-2.5 text-right font-medium text-gray-800">Rp {{ number_format($sku->total_omset, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-gray-400 text-[11px]">Tidak ada data penjualan pada rentang waktu ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- BAGIAN KIRI: DETEKTOR PESANAN BELUM CAIR (UNMATCHED) -->
        <div class="p-5 bg-white rounded-lg shadow-sm border border-amber-200 flex flex-col h-[400px]">
            <div class="mb-4">
                <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-amber-500 rounded-full"></span>
                    Audit: Pesanan Menggantung / Belum Cair
                </h4>
                <p class="text-[11px] text-gray-500">Order ID terdeteksi di data penjualan, tapi dananya belum dilepas ke laporan Excel Income.</p>
            </div>

            <!-- KUNCI TINGGI DI SINI: Ditambahkan max-h dan overflow-y-auto -->
            <div class="overflow-x-auto overflow-y-auto flex-grow max-h-64 border border-gray-100 rounded">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-amber-50 text-amber-800 font-semibold uppercase sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-3 py-2 text-left bg-amber-50">ID Pesanan</th>
                            <th class="px-4 py-3">Tanggal Pesanan</th>
                            <th class="px-3 py-2 text-left bg-amber-50">Status Pesanan</th>
                            <th class="px-3 py-2 text-right bg-amber-50">Nilai Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse($pesananBelumCairList as $pbc)
                            <tr class="hover:bg-amber-50/30">
                                <td class="px-4 py-3 text-center font-medium text-gray-500 bg-gray-50/50">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-3 py-2.5 font-mono text-indigo-600">{{ $pbc->order_id }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ \Carbon\Carbon::parse($pbc->created_time)->format('d M Y H:i') }}
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700">
                                        {{ $pbc->order_status }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-right font-medium text-gray-800">Rp {{ number_format($pbc->order_amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-3 py-4 text-center text-gray-400 text-[11px]">Semua dana pesanan klop / sudah cair!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BAGIAN KANAN: ANOMALI ONGKIR -->
        <div class="p-5 bg-white rounded-lg shadow-sm border border-red-100 flex flex-col h-[400px]">
            <div class="mb-4">
                <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-ping"></span>
                    Detektor Kebocoran & Selisih Ongkir
                </h4>
                <p class="text-[11px] text-gray-500">Klaim ke logistik jika bengkak tidak wajar.</p>
            </div>

            <!-- KUNCI TINGGI DI SINI: Ditambahkan max-h dan overflow-y-auto -->
            <div class="overflow-x-auto overflow-y-auto flex-grow max-h-64 border border-gray-100 rounded">
                <table class="min-w-full divide-y divide-gray-200 text-xs">
                    <thead class="bg-red-50 text-red-800 font-semibold uppercase sticky top-0 z-10">
                        <tr>
                            <th class="px-3 py-2 text-left bg-red-50">ID Pesanan</th>
                            <th class="px-3 py-2 text-right bg-red-50">Estimasi</th>
                            <th class="px-3 py-2 text-right bg-red-50">Riil</th>
                            <th class="px-3 py-2 text-right bg-red-50">Kerugian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse($anomaliOngkirList as $anomali)
                            <tr class="{{ $anomali->rasio_bengkak >= 2 ? 'bg-red-50 hover:bg-red-100/70' : 'bg-amber-50/60 hover:bg-amber-100/50' }} transition">
                                <td class="px-3 py-2.5 font-mono font-medium text-gray-800">{{ $anomali->order_id }}</td>
                                <td class="px-3 py-2.5 text-right text-gray-500">Rp {{ number_format($anomali->estimasi, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-right font-medium text-gray-900">Rp {{ number_format($anomali->riil, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-red-600">- Rp {{ number_format($anomali->selisih_rugi, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-gray-400 text-[11px]">Aman! Tidak ada kebocoran ongkir.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>