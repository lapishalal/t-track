<div class="p-6 bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Pusat Unggah Berkas TikTok Shop</h2>
            <p class="text-sm text-gray-500 mt-1">Isi nama toko sekali, lalu unggah file pesanan dan income untuk toko yang sama.</p>
        </div>
        <div class="rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-xs text-indigo-800 max-w-md">
            <p class="font-bold">Urutan yang disarankan</p>
            <p class="mt-1">Unggah file pesanan terlebih dahulu, kemudian file income. Ini membantu pencocokan order, dana cair, ongkir, dan retur/refund.</p>
        </div>
    </div>

    <!-- Form Input Nama Toko -->
    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
        <label class="block text-sm font-bold text-gray-800 mb-2">1. Nama Toko / Tenant</label>
        <input type="text" wire:model.live.debounce.400ms="shop_name" placeholder="Contoh: MS Glow Official, Pinang Living"
               class="w-full max-w-xl px-4 py-2.5 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        <p class="mt-2 text-xs text-gray-500">Nama ini akan disimpan ke semua baris order dan income. Pastikan sama untuk file pesanan dan income toko tersebut.</p>
        @error('shop_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Box Kiri: Upload Berkas Semua Pesanan -->
        <div class="border border-indigo-100 rounded-lg p-5 bg-indigo-50/40">
            <div class="mb-4">
                <span class="inline-flex px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase">Langkah 2A</span>
                <h3 class="font-semibold text-gray-800 mt-2">File Semua Pesanan</h3>
                <p class="text-xs text-gray-500 mt-1">Terima `.csv`, `.xlsx`, atau `.xls`. Format wajib punya kolom Order ID, Order Status, dan SKU ID.</p>
            </div>

            @if (session()->has('success_order'))
                <div class="p-3 bg-green-100 text-green-800 rounded mb-3 text-sm font-medium">
                    {{ session('success_order') }}
                </div>
            @endif
            @if (session()->has('error_order'))
                <div class="p-3 bg-red-100 text-red-800 rounded mb-3 text-sm">
                    {{ session('error_order') }}
                </div>
            @endif

            <form wire:submit.prevent="processOrder">
                <input type="file" wire:model="file_order" accept=".csv,.xlsx,.xls" class="w-full rounded-md border border-dashed border-indigo-200 bg-white p-3 text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('file_order') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <button type="submit" wire:loading.attr="disabled" @disabled(blank($shop_name) || !$file_order) class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-bold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Proses File Pesanan
                    </button>
                    <div class="text-xs">
                        @if(blank($shop_name))
                            <span class="text-amber-700">Isi nama toko terlebih dahulu.</span>
                        @elseif(!$file_order)
                            <span class="text-gray-500">Pilih file pesanan untuk mengaktifkan tombol.</span>
                        @endif
                        <span wire:loading wire:target="file_order" class="text-amber-600 animate-pulse">Mengunggah ke server...</span>
                        <span wire:loading wire:target="processOrder" class="text-indigo-600 font-medium">Sedang memproses data...</span>
                    </div>
                </div>
            </form>
        </div>

        <!-- Box Kanan: Upload Berkas Income -->
        <div class="border border-emerald-100 rounded-lg p-5 bg-emerald-50/40">
            <div class="mb-4">
                <span class="inline-flex px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase">Langkah 2B</span>
                <h3 class="font-semibold text-gray-800 mt-2">File Laporan Keuangan / Income</h3>
                <p class="text-xs text-gray-500 mt-1">Gunakan export income yang memiliki sheet `Detail pesanan`.</p>
            </div>

            @if (session()->has('success_income'))
                <div class="p-3 bg-green-100 text-green-800 rounded mb-3 text-sm font-medium">
                    {{ session('success_income') }}
                </div>
            @endif
            @if (session()->has('error_income'))
                <div class="p-3 bg-red-100 text-red-800 rounded mb-3 text-sm">
                    {{ session('error_income') }}
                </div>
            @endif

            <form wire:submit.prevent="processIncome">
                <input type="file" wire:model="file_income" accept=".xlsx,.xls,.csv" class="w-full rounded-md border border-dashed border-emerald-200 bg-white p-3 text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                @error('file_income') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <button type="submit" wire:loading.attr="disabled" @disabled(blank($shop_name) || !$file_income) class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-sm font-bold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Proses File Income
                    </button>
                    <div class="text-xs">
                        @if(blank($shop_name))
                            <span class="text-amber-700">Isi nama toko terlebih dahulu.</span>
                        @elseif(!$file_income)
                            <span class="text-gray-500">Pilih file income untuk mengaktifkan tombol.</span>
                        @endif
                        <span wire:loading wire:target="file_income" class="text-amber-600 animate-pulse">Mengunggah ke server...</span>
                        <span wire:loading wire:target="processIncome" class="text-emerald-600 font-medium">Sedang memproses dana...</span>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <!-- ============ HISTORI UPLOAD ============ -->
    <div class="mt-8 border-t border-gray-200 pt-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 text-sm">Histori Upload Terakhir</h3>
                <p class="text-[11px] text-gray-500">50 unggahan terakhir yang tercatat di sistem</p>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-xl">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-gray-700 font-bold text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">Nama File</th>
                        <th class="px-4 py-3 text-center">Tipe</th>
                        <th class="px-4 py-3 text-left">Toko</th>
                        <th class="px-4 py-3 text-right">Jumlah Row</th>
                        <th class="px-4 py-3 text-left font-mono">Batch ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-600">
                    @forelse($uploadLogs as $log)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-4 py-3 text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-xs font-medium text-gray-800 max-w-xs truncate" title="{{ $log->file_name }}">{{ $log->file_name }}</td>
                            <td class="px-4 py-3 text-center">
                                @if(str_contains(strtolower($log->file_type), 'order') || str_contains(strtolower($log->file_name), 'pesanan'))
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase">Order</span>
                                @elseif(str_contains(strtolower($log->file_type), 'income') || str_contains(strtolower($log->file_name), 'income'))
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full uppercase">Income</span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-full uppercase">{{ $log->file_type ?? 'Lainnya' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $log->shop_name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-gray-800">{{ number_format($log->total_rows_imported, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-xs font-mono text-gray-400">{{ $log->batch_id ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span class="text-gray-400 text-sm font-medium">Belum ada histori upload.</span>
                                    <span class="text-gray-300 text-xs">Unggah file pesanan atau income untuk melihat riwayat.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
