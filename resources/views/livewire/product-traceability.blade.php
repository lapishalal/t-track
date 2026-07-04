<div class="p-6 bg-white rounded-xl shadow-md border border-gray-100" x-data="{ filterOpen: false }">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">Alur Keluar Produk & Traceability</h2>
            <p class="text-xs text-gray-500 mt-1">Lacak distribusi pengiriman wilayah produk dan ekspor laporan yang sudah bersih ke Excel.</p>
        </div>
        
        <!-- Tombol Clean Export -->
        <button wire:click="exportCleanReport" 
                class="group inline-flex items-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-lg text-sm font-semibold shadow-lg shadow-emerald-200 hover:shadow-emerald-300 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0">
            <svg class="w-4 h-4 mr-2 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download Clean Report (Excel)
        </button>
    </div>

    <!-- ==================== PANEL FILTER INTERAKTIF ==================== -->
    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-100 rounded-xl p-1 mb-6 overflow-hidden">
        <!-- Header Panel (Toggle) -->
        <div class="flex items-center justify-between p-3 cursor-pointer select-none rounded-lg hover:bg-white/60 transition-colors duration-200"
             @click="filterOpen = !filterOpen">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center shadow-md shadow-indigo-200">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-sm">Export dengan Filter Lanjutan</h3>
                    <p class="text-[11px] text-gray-500">Saring data dulu, baru download</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400 font-medium hidden md:inline" x-show="!filterOpen">Klik untuk buka</span>
                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm transition-transform duration-300" :class="filterOpen ? 'rotate-180' : ''">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Isi Panel -->
        <div x-show="filterOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform -translate-y-2"
             class="px-3 pb-3 pt-1">
            
            <!-- Filter Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                <!-- Status Pesanan -->
                <div class="group">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-gray-700 mb-1.5">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Status Pesanan
                    </label>
                    <div class="relative">
                        <select wire:model="filterStatus" class="appearance-none w-full bg-white border-2 border-gray-200 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 hover:border-indigo-300 cursor-pointer">
                            <option value="">📋 Semua Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Tanggal Pesanan -->
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-gray-700 mb-1.5">
                        <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Tanggal Pesanan
                    </label>
                    <div class="relative">
                        <select wire:model.live="filterOrderDateType" class="appearance-none w-full bg-white border-2 border-gray-200 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200 hover:border-green-300 cursor-pointer">
                            <option value="">📅 Semua Tanggal</option>
                            <option value="today">☀️ Hari Ini</option>
                            <option value="this_week">📆 Minggu Ini</option>
                            <option value="this_month">🗓️ Bulan Ini</option>
                            <option value="last_month">📉 Bulan Lalu</option>
                            <option value="custom">🎯 Pilih Rentang</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Tanggal Cair -->
                <div>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-gray-700 mb-1.5">
                        <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tanggal Cair
                    </label>
                    <div class="relative">
                        <select wire:model.live="filterPayoutDateType" class="appearance-none w-full bg-white border-2 border-gray-200 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 hover:border-purple-300 cursor-pointer">
                            <option value="">💰 Semua Tanggal</option>
                            <option value="today">☀️ Hari Ini</option>
                            <option value="this_week">📆 Minggu Ini</option>
                            <option value="this_month">🗓️ Bulan Ini</option>
                            <option value="last_month">📉 Bulan Lalu</option>
                            <option value="custom">🎯 Pilih Rentang</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Date Range Inputs -->
            @if($filterOrderDateType === 'custom' || $filterPayoutDateType === 'custom')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3 animate-pulse-once">
                @if($filterOrderDateType === 'custom')
                <div class="bg-white/80 rounded-lg p-3 border border-green-200">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-green-700 mb-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Rentang Tanggal Pesanan
                    </label>
                    <div class="flex gap-2">
                        <input type="date" wire:model="filterOrderDateFrom" class="w-full bg-white border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200 hover:border-green-300">
                        <span class="text-gray-400 self-center font-bold">→</span>
                        <input type="date" wire:model="filterOrderDateTo" class="w-full bg-white border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200 hover:border-green-300">
                    </div>
                </div>
                @endif
                @if($filterPayoutDateType === 'custom')
                <div class="bg-white/80 rounded-lg p-3 border border-purple-200">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-purple-700 mb-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Rentang Tanggal Cair
                    </label>
                    <div class="flex gap-2">
                        <input type="date" wire:model="filterPayoutDateFrom" class="w-full bg-white border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 hover:border-purple-300">
                        <span class="text-gray-400 self-center font-bold">→</span>
                        <input type="date" wire:model="filterPayoutDateTo" class="w-full bg-white border-2 border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 hover:border-purple-300">
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-3 mt-4 pt-3 border-t border-indigo-100">
                <button wire:click="resetFilters" 
                        class="group flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200">
                    <svg class="w-3.5 h-3.5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset Semua Filter
                </button>
                
                <div class="flex gap-2 w-full sm:w-auto">
                    <button wire:click="applyFilters" 
                            wire:loading.attr="disabled"
                            class="group flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white rounded-lg text-sm font-bold shadow-lg shadow-blue-200 hover:shadow-blue-300 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0">
                        <svg wire:loading wire:target="applyFilters" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg wire:loading.remove wire:target="applyFilters" class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span wire:loading.remove wire:target="applyFilters">Tampilkan Data</span>
                        <span wire:loading wire:target="applyFilters">Memuat...</span>
                    </button>
                    
                    <button wire:click="exportFilteredReport" 
                            wire:loading.attr="disabled"
                            class="group flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg text-sm font-bold shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0">
                        <svg wire:loading wire:target="exportFilteredReport" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg wire:loading.remove wire:target="exportFilteredReport" class="w-4 h-4 mr-2 group-hover:translate-y-0.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span wire:loading.remove wire:target="exportFilteredReport">Download Excel</span>
                        <span wire:loading wire:target="exportFilteredReport">Mengunduh...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== BADGE FILTER AKTIF ==================== -->
    @if($filterStatus || $filterOrderDateType || $filterPayoutDateType)
    <div class="flex flex-wrap items-center gap-2 mb-4 p-3 bg-blue-50/50 rounded-lg border border-blue-100">
        <span class="flex items-center gap-1 text-xs font-bold text-blue-700">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filter Aktif:
        </span>
        @if($filterStatus)
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-xs rounded-full font-bold shadow-sm shadow-blue-200">
                {{ $filterStatus }}
                <button wire:click="$set('filterStatus', '')" class="hover:bg-blue-700 rounded-full p-0.5 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </span>
        @endif
        @if($filterOrderDateType)
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-600 text-white text-xs rounded-full font-bold shadow-sm shadow-green-200">
                Pesanan: 
                @if($filterOrderDateType === 'today') Hari Ini
                @elseif($filterOrderDateType === 'this_week') Minggu Ini
                @elseif($filterOrderDateType === 'this_month') Bulan Ini
                @elseif($filterOrderDateType === 'last_month') Bulan Lalu
                @elseif($filterOrderDateType === 'custom') {{ $filterOrderDateFrom ?? '...' }} → {{ $filterOrderDateTo ?? '...' }}
                @endif
                <button wire:click="$set('filterOrderDateType', ''); $set('filterOrderDateFrom', ''); $set('filterOrderDateTo', '')" class="hover:bg-green-700 rounded-full p-0.5 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </span>
        @endif
        @if($filterPayoutDateType)
            <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-600 text-white text-xs rounded-full font-bold shadow-sm shadow-purple-200">
                Cair: 
                @if($filterPayoutDateType === 'today') Hari Ini
                @elseif($filterPayoutDateType === 'this_week') Minggu Ini
                @elseif($filterPayoutDateType === 'this_month') Bulan Ini
                @elseif($filterPayoutDateType === 'last_month') Bulan Lalu
                @elseif($filterPayoutDateType === 'custom') {{ $filterPayoutDateFrom ?? '...' }} → {{ $filterPayoutDateTo ?? '...' }}
                @endif
                <button wire:click="$set('filterPayoutDateType', ''); $set('filterPayoutDateFrom', ''); $set('filterPayoutDateTo', '')" class="hover:bg-purple-700 rounded-full p-0.5 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </span>
        @endif
    </div>
    @endif

    <!-- ==================== FILTER & PENCARIAN ==================== -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" wire:model.live="search" placeholder="Cari Resi, Order ID, atau Nama Produk..." 
                   class="pl-10 border-2 border-gray-200 rounded-lg text-sm px-4 py-2.5 w-full focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 hover:border-gray-300">
        </div>
        
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <select wire:model.live="selectedMonth" class="appearance-none pl-10 border-2 border-gray-200 rounded-lg text-sm px-4 py-2.5 w-full focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all duration-200 hover:border-gray-300 cursor-pointer bg-white">
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
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
    </div>

    <!-- ==================== TABEL DATA ==================== -->
    <div class="overflow-x-auto border-2 border-gray-200 rounded-xl shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-700 font-bold text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Waktu Pesanan</th>
                    <th class="px-4 py-3 text-left">Order ID</th>
                    <th class="px-4 py-3 text-left">Nama Produk</th>
                    <th class="px-4 py-3 text-left">Tujuan Pengiriman</th>
                    <th class="px-4 py-3 text-left">No. Resi (Tracking ID)</th>
                    <th class="px-4 py-3 text-center">Status Pencairan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-gray-600">
                @forelse($ordersData as $order)
                    <tr class="hover:bg-indigo-50/40 transition-colors duration-150 group">
                        <td class="px-4 py-3 text-xs font-medium">{{ $order->created_time }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-indigo-600 font-semibold group-hover:text-indigo-800 transition-colors cursor-pointer"
                            wire:click="showOrderDetail('{{ $order->order_id }}')"
                            title="Klik untuk lihat detail">
                            <span class="flex items-center gap-1 hover:underline">
                                {{ $order->order_id }}
                                <svg class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-xs truncate font-medium text-gray-800">{{ $order->product_name }}</td>
                        <td class="px-4 py-3 text-xs">
                            {{ $order->province ?? '-' }}, {{ $order->regency_city ?? '-' }}
                        </td>
                        <!-- Fitur Salin Cepat Alpine.js -->
                        <td class="px-4 py-3 text-xs font-mono" x-data="{ copied: false }">
                            @if($order->tracking_id)
                                <button @click="navigator.clipboard.writeText('{{ $order->tracking_id }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                                        class="hover:text-indigo-600 flex items-center gap-1 focus:outline-none px-2 py-1 rounded hover:bg-indigo-50 transition-colors">
                                    <span>{{ $order->tracking_id }}</span>
                                    <span x-show="copied" class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-bold">Tersalin!</span>
                                </button>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($order->income)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Selesai / Cair
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                                    <svg class="w-3 h-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Menggantung
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-gray-400 font-medium">Data pelacakan alur produk tidak ditemukan.</span>
                                <span class="text-gray-300 text-xs">Coba ubah filter atau periode pencarian</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ordersData->links() }}
    </div>


    <!-- ============ MODAL DETAIL ORDER ============ -->
    @if($showOrderModal && $selectedOrder)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeOrderDetail"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 sm:px-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detail Order
                        </h3>
                        <button wire:click="closeOrderDetail" class="text-white hover:text-indigo-100 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-indigo-100 font-medium">Order ID: <span class="font-mono">{{ $selectedOrder->order_id }}</span></p>
                </div>
                <div class="px-4 py-4 sm:px-6 max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Toko</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedOrder->shop_name }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Status Pesanan</p>
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">{{ $selectedOrder->order_status }}</span>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Tanggal Pesanan</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedOrder->created_time ? \Carbon\Carbon::parse($selectedOrder->created_time)->format('d M Y H:i') : '-' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Quantity</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedOrder->quantity }} pcs</p>
                        </div>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-3 mb-4">
                        <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Produk</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $selectedOrder->product_name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">SKU: <span class="font-mono text-indigo-600">{{ $selectedOrder->sku_id }}</span></p>
                        @if($selectedOrder->variation)
                            <p class="text-xs text-gray-500 mt-0.5">Varian: {{ $selectedOrder->variation }}</p>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="border border-gray-200 rounded-lg p-3">
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Pembeli</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $selectedOrder->buyer_username ?? '-' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $selectedOrder->province ?? '-' }}, {{ $selectedOrder->regency_city ?? '-' }}</p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-3">
                            <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Resi</p>
                            <p class="text-sm font-mono font-semibold text-indigo-600">{{ $selectedOrder->tracking_id ?? '-' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Ongkir Estimasi: Rp {{ number_format($selectedOrder->shipping_fee_estimated, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-lg p-4">
                        <p class="text-[10px] text-emerald-600 uppercase font-bold mb-2">Ringkasan Finansial</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div class="flex justify-between"><span class="text-gray-600">Omset Kotor</span><span class="font-semibold text-gray-800">Rp {{ number_format($selectedOrder->order_amount, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">HPP</span><span class="font-semibold text-gray-800">Rp {{ number_format(($selectedOrder->hpp_amount ?? 0) * $selectedOrder->quantity, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Overhead</span><span class="font-semibold text-gray-800">Rp {{ number_format(($selectedOrder->overhead_per_pack ?? 0) * $selectedOrder->quantity, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between border-t border-emerald-200 pt-2 mt-1"><span class="text-gray-600">Dana Cair</span><span class="font-semibold text-gray-800">Rp {{ number_format($selectedOrder->income ? $selectedOrder->income->disbursement_amount : 0, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between col-span-2">
                                @php
                                    $cair = $selectedOrder->income ? $selectedOrder->income->disbursement_amount : 0;
                                    $hppTotal = ($selectedOrder->hpp_amount ?? 0) * $selectedOrder->quantity;
                                    $overheadTotal = ($selectedOrder->overhead_per_pack ?? 0) * $selectedOrder->quantity;
                                    $profit = $cair - ($hppTotal + $overheadTotal);
                                @endphp
                                <span class="text-gray-700 font-bold">Profit Bersih</span>
                                <span class="font-bold {{ $profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($profit, 0, ',', '.') }}</span>
                            </div>
                            @if($selectedOrder->income && $selectedOrder->income->payout_time)
                            <div class="flex justify-between col-span-2 text-xs text-gray-500">
                                <span>Tanggal Cair:</span>
                                <span>{{ \Carbon\Carbon::parse($selectedOrder->income->payout_time)->format('d M Y H:i') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="closeOrderDetail" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-gray-800 text-base font-medium text-white hover:bg-gray-900 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
