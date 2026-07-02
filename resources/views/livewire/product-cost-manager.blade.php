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
                                <button wire:click="startEdit('{{ $product->sku_id }}', {{ $product->hpp_amount }}, {{ $product->overhead_per_pack }})" 
                                        class="px-3 py-1 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded text-xs font-medium border border-indigo-200">
                                    Set HPP
                                </button>
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
</div>