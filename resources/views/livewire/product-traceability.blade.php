<div class="p-6 bg-white rounded-lg shadow-sm">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Alur Keluar Produk & Traceability</h2>
            <p class="text-xs text-gray-500">Lacak distribusi pengiriman wilayah produk dan ekspor laporan yang sudah bersih ke Excel.</p>
        </div>
        
        <!-- Tombol Clean Export -->
        <button wire:click="exportCleanReport" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-sm font-medium shadow-sm transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download Clean Report (Excel)
        </button>
    </div>

    <!-- Filter & Pencarian -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <input type="text" wire:model.live="search" placeholder="Cari Resi, SKU, atau Nama Produk..." class="border border-gray-300 rounded-md text-sm px-4 py-2 w-full">
        
        <select wire:model.live="selectedMonth" class="border border-gray-300 rounded-md text-sm px-4 py-2 w-full">
            <option value="">-- Semua Bulan --</option>
            <option value="1">Januari</option>
            <option value="2">Februari</option>
            <option value="3">Maret</option>
            <option value="4">April</option>
            <option value="5">Mei</option>
            <option value="6">Juni</option>
            <option value="7">Juli</option>
            <option value="8">Agustus</option>
            <option value="9">September</option>
            <option value="10">Oktber</option>
            <option value="11">November</option>
            <option value="12">Desember</option>
        </select>
    </div>

    <!-- Tabel Data -->
    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-700 font-medium text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Waktu Pesanan</th>
                    <th class="px-4 py-3 text-left">SKU ID</th>
                    <th class="px-4 py-3 text-left">Nama Produk</th>
                    <th class="px-4 py-3 text-left">Tujuan Pengiriman</th>
                    <th class="px-4 py-3 text-left">No. Resi (Tracking ID)</th>
                    <th class="px-4 py-3 text-center">Status Pencairan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-600">
                @forelse($ordersData as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-xs">{{ $order->created_time }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-indigo-600">{{ $order->sku_id }}</td>
                        <td class="px-4 py-3 max-w-xs truncate font-medium text-gray-800">{{ $order->product_name }}</td>
                        <td class="px-4 py-3 text-xs">
                            {{ $order->province ?? '-' }}, {{ $order->regency_city ?? '-' }}
                        </td>
                        <!-- Fitur Salin Cepat Alpine.js -->
                        <td class="px-4 py-3 text-xs font-mono" x-data="{ copied: false }">
                            @if($order->tracking_id)
                                <button @click="navigator.clipboard.writeText('{{ $order->tracking_id }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                        class="hover:text-indigo-600 flex items-center gap-1 focus:outline-none">
                                    <span>{{ $order->tracking_id }}</span>
                                    <span x-show="copied" class="text-[10px] bg-indigo-100 text-indigo-700 px-1 rounded">Tersalin!</span>
                                </button>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($order->income)
                                <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-medium">Selesai / Cair</span>
                            @else
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-medium">Menggantung</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">Data pelacakan alur produk tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ordersData->links() }}
    </div>
</div>