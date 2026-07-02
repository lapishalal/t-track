<div class="p-6 bg-white rounded-lg shadow-sm">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Pusat Unggah Berkas TikTok Shop</h2>

    <!-- Form Input Nama Toko -->
    <div class="mb-6 max-w-md">
        <label class="block text-sm font-medium text-gray-700 mb-2">1. Masukkan Nama Toko / Tenant</label>
        <input type="text" wire:model="shop_name" placeholder="Contoh: MS Glow Official, Pinang Living" 
               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
        @error('shop_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Box Kiri: Upload Berkas Semua Pesanan -->
        <div class="border border-gray-200 rounded-lg p-5 bg-gray-50">
            <h3 class="font-medium text-gray-700 mb-2">2a. File Semua Pesanan (.csv)</h3>
            <p class="text-xs text-gray-500 mb-4">Gunakan file berkode nama depan "Semua pesanan-xxx"</p>

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
                <input type="file" wire:model="file_order" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                @error('file_order') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                <div class="mt-4 flex items-center justify-between">
                    <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium shadow-sm disabled:opacity-50">
                        Proses File Pesanan
                    </button>
                    <span wire:loading wire:target="file_order" class="text-xs text-amber-600 animate-pulse">Mengunggah ke server...</span>
                    <span wire:loading wire:target="processOrder" class="text-xs text-indigo-600 font-medium">Sedang memproses data...</span>
                </div>
            </form>
        </div>

        <!-- Box Kanan: Upload Berkas Income -->
        <div class="border border-gray-200 rounded-lg p-5 bg-gray-50">
            <h3 class="font-medium text-gray-700 mb-2">2b. File Laporan Keuangan / Income (.xlsx)</h3>
            <p class="text-xs text-gray-500 mb-4">Gunakan file berkode nama depan "income_xxxx"</p>

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
                <input type="file" wire:model="file_income" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                @error('file_income') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror

                <div class="mt-4 flex items-center justify-between">
                    <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-sm font-medium shadow-sm disabled:opacity-50">
                        Proses File Income
                    </button>
                    <span wire:loading wire:target="file_income" class="text-xs text-amber-600 animate-pulse">Mengunggah ke server...</span>
                    <span wire:loading wire:target="processIncome" class="text-xs text-emerald-600 font-medium">Sedang memproses dana...</span>
                </div>
            </form>
        </div>

    </div>
</div>