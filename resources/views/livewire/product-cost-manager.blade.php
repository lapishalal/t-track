<div class="p-6 bg-white rounded-lg shadow-sm">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Kamus Produk & Pengaturan HPP</h2>
            <p class="text-xs text-gray-500">Isi HPP dan Biaya Operasional (Overhead packing, lakban, dll) agar kalkulasi keuntungan bersih akurat.</p>
        </div>
        <!-- Kotak Pencarian -->
        <div class="w-full md:w-72">
            <input type="text" wire:model.live="search" placeholder="Cari SKU, nama produk atau variasi..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    @if (session()->has('success_cost'))
        <div class="p-3 bg-green-100 text-green-800 rounded mb-4 text-sm font-medium">
            {{ session('success_cost') }}
        </div>
    @endif

    <!-- ============ SLOW-MOVING / DEAD STOCK ALERT ============ -->
    @if($slowMovingSkus->count() > 0 || $neverSoldSkus->count() > 0)
    <div class="mb-4 border border-amber-200 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 overflow-hidden">
        <div class="px-4 py-3 flex items-center justify-between cursor-pointer" onclick="this.nextElementSibling.classList.toggle('hidden')">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">⚠️ Peringatan Stagnan & Stok Mati</h3>
                    <p class="text-[11px] text-gray-500">SKU yang tidak terjual > 30 hari atau belum pernah terjual.</p>
                </div>
            </div>
            <div class="flex gap-2">
                @if($slowMovingSkus->count() > 0)
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full">{{ $slowMovingSkus->count() }} Slow-Moving</span>
                @endif
                @if($neverSoldSkus->count() > 0)
                    <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-full">{{ $neverSoldSkus->count() }} Belum Pernah Terjual</span>
                @endif
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
        <div class="hidden">
            <div class="overflow-x-auto max-h-64 border-t border-amber-100">
                <table class="min-w-full divide-y divide-amber-100 text-xs">
                    <thead class="bg-amber-100/50 text-amber-800 font-semibold uppercase">
                        <tr>
                            <th class="px-3 py-2 text-left">SKU ID</th>
                            <th class="px-3 py-2 text-left">Nama Produk</th>
                            <th class="px-3 py-2 text-center">Status</th>
                            <th class="px-3 py-2 text-center">Terakhir Jual</th>
                            <th class="px-3 py-2 text-right">Total Terjual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-50 text-gray-600">
                        @foreach($neverSoldSkus as $sku)
                            <tr class="bg-red-50/30 hover:bg-red-50/50 transition">
                                <td class="px-3 py-2 font-mono text-[10px] text-indigo-600">{{ $sku->sku_id }}</td>
                                <td class="px-3 py-2 truncate max-w-xs">{{ $sku->product_name }}</td>
                                <td class="px-3 py-2 text-center"><span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-[9px] font-bold rounded-full">Belum Pernah</span></td>
                                <td class="px-3 py-2 text-center text-gray-400">-</td>
                                <td class="px-3 py-2 text-right text-gray-400">-</td>
                            </tr>
                        @endforeach
                        @foreach($slowMovingSkus as $sku)
                            @php
                                $daysAgo = \Carbon\Carbon::parse($sku->last_order_date)->diffInDays(now());
                                $badgeColor = $daysAgo > 60 ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700';
                            @endphp
                            <tr class="hover:bg-amber-50/30 transition">
                                <td class="px-3 py-2 font-mono text-[10px] text-indigo-600">{{ $sku->sku_id }}</td>
                                <td class="px-3 py-2 truncate max-w-xs">{{ $sku->product_name }}</td>
                                <td class="px-3 py-2 text-center"><span class="px-1.5 py-0.5 {{ $badgeColor }} text-[9px] font-bold rounded-full">{{ $daysAgo }} hari lalu</span></td>
                                <td class="px-3 py-2 text-center text-gray-500">{{ \Carbon\Carbon::parse($sku->last_order_date)->format('d M Y') }}</td>
                                <td class="px-3 py-2 text-right font-medium">{{ number_format($sku->total_sold, 0, ',', '.') }} pcs</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
        <div class="p-3 bg-green-100 text-green-800 rounded mb-4 text-sm font-medium">
            {{ session('success_cost') }}
        </div>
    @endif

    <div class="overflow-x-auto border border-gray-200 rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-700 font-medium uppercase text-xs">
                <tr>
                    <th wire:click="sortBy('sku_id')" class="px-6 py-3 text-left cursor-pointer hover:bg-gray-100 transition">
                        SKU ID {{$sortField === 'sku_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : ''}}
                    </th>
                    <th wire:click="sortBy('product_name')" class="px-6 py-3 text-left cursor-pointer hover:bg-gray-100 transition">
                        Nama Produk {{$sortField === 'product_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : ''}}
                    </th>
                    <th class="px-6 py-3 text-left">Toko</th>
                    <th wire:click="sortBy('hpp_amount')" class="px-6 py-3 text-left cursor-pointer hover:bg-gray-100 transition text-indigo-600 font-bold">
                        HPP (Rp) {{$sortField === 'hpp_amount' ? ($sortDirection === 'asc' ? '▲' : '▼') : ''}}
                    </th>
                    <th wire:click="sortBy('overhead_per_pack')" class="px-6 py-3 text-left cursor-pointer hover:bg-gray-100 transition">
                        Overhead {{$sortField === 'overhead_per_pack' ? ($sortDirection === 'asc' ? '▲' : '▼') : ''}}
                    </th>
                    <th class="px-6 py-3 text-center w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-600">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono text-xs text-indigo-600">{{ $product->sku_id }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-800">{{ $product->product_name }}</div>
                            @if($product->variation)
                                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded mt-1 inline-block">Varian: {{ $product->variation }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">{{ $product->shop_name }}</td>
                        
                        <!-- Logika Form Inline Edit -->
                        @if($editingSku === $product->sku_id)
                            <!-- Mode Edit Aktif -->
                            <td class="px-4 py-2">
                                <input type="number" wire:model="hpp_amount" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" wire:model="overhead_per_pack" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-indigo-500">
                            </td>
                            <td class="px-6 py-4 text-center space-x-1">
                                <button wire:click="saveEdit" class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-medium">Simpan</button>
                                <button wire:click="cancelEdit" class="px-2 py-1 bg-gray-400 hover:bg-gray-500 text-white rounded text-xs">Batal</button>
                            </td>
                        @else
                            <!-- Mode Tampilan Biasa -->
                            <td class="px-6 py-4 font-medium text-gray-800">Rp {{ number_format($product->hpp_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-gray-700">Rp {{ number_format($product->overhead_per_pack, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col gap-1.5 items-center">
                                    <button wire:click="startEdit('{{ $product->sku_id }}', {{ $product->hpp_amount }}, {{ $product->overhead_per_pack }})" 
                                            class="px-3 py-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded text-xs font-medium border border-indigo-200 transition-colors w-full">
                                        Set HPP
                                    </button>
                                    <button wire:click="showHistory('{{ $product->sku_id }}')" 
                                            class="px-3 py-1 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded text-xs font-medium border border-amber-200 transition-colors w-full flex items-center justify-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Histori
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada data produk terdaftar. Silakan unggah file pesanan terlebih dahulu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Bawaan Laravel Tailwind -->
    <div class="mt-4">
        {{ $products->links() }}
    </div>

    <!-- ============ MODAL HISTORI HPP ============ -->
    @if($showHistoryModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeHistory"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-3 sm:px-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Histori Perubahan HPP
                        </h3>
                        <button wire:click="closeHistory" class="text-white hover:text-amber-100 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-amber-100 font-medium">SKU: <span class="font-mono">{{ $historySku }}</span></p>
                </div>
                <div class="px-4 py-4 sm:px-6 max-h-96 overflow-y-auto">
                    @if(count($historyRecords) > 0)
                        <div class="space-y-3">
                            @foreach($historyRecords as $record)
                                <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 hover:bg-white transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-gray-500">{{ \Carbon\Carbon::parse($record->created_at)->format('d M Y H:i') }}</span>
                                        <span class="text-[10px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">{{ $record->changed_by }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-[10px] text-gray-400 uppercase font-bold">HPP</p>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-gray-400 line-through">Rp {{ number_format($record->hpp_amount_old, 0, ',', '.') }}</span>
                                                <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                <span class="font-bold text-indigo-600">Rp {{ number_format($record->hpp_amount_new, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 uppercase font-bold">Overhead</p>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-gray-400 line-through">Rp {{ number_format($record->overhead_per_pack_old, 0, ',', '.') }}</span>
                                                <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                <span class="font-bold text-indigo-600">Rp {{ number_format($record->overhead_per_pack_new, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-gray-400 text-sm font-medium">Belum ada histori perubahan untuk SKU ini.</p>
                        </div>
                    @endif
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeHistory" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-white hover:bg-gray-900 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>