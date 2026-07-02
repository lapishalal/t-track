<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pusat Komando Analytics T-Track') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ currentTab: 'analytics' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Menu Navigasi Tab Baku -->
            <div class="border-b border-gray-200 bg-white rounded-t-lg p-2 flex flex-wrap gap-2 shadow-sm">
                <button @click="currentTab = 'analytics'" 
                        :class="currentTab === 'analytics' ? 'bg-indigo-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-4 py-2 rounded-md text-sm transition">
                    📊 Ringkasan Analytics
                </button>
                <button @click="currentTab = 'upload'" 
                        :class="currentTab === 'upload' ? 'bg-indigo-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-4 py-2 rounded-md text-sm transition">
                    📁 Unggah Berkas TikTok
                </button>
                <button @click="currentTab = 'hpp'" 
                        :class="currentTab === 'hpp' ? 'bg-indigo-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-4 py-2 rounded-md text-sm transition">
                    🏷️ Kamus & Atur HPP
                </button>
                <button @click="currentTab = 'traceability'" 
                        :class="currentTab === 'traceability' ? 'bg-indigo-600 text-white font-medium' : 'text-gray-600 hover:bg-gray-100'"
                        class="px-4 py-2 rounded-md text-sm transition">
                    📦 Alur Keluar & Resi
                </button>
            </div>

            <!-- Konten Dinamis Berdasarkan Tab Terpilih -->
            <div class="space-y-6">
                <!-- Tab 1: Analytics -->
                <div x-show="currentTab === 'analytics'" x-transition>
                    @livewire('financial-dashboard')
                </div>

                <!-- Tab 2: Upload -->
                <div x-show="currentTab === 'upload'" x-transition>
                    @livewire('upload-manager')
                </div>

                <!-- Tab 3: Kamus HPP -->
                <div x-show="currentTab === 'hpp'" x-transition>
                    @livewire('product-cost-manager')
                </div>

                <!-- Tab 4: Traceability -->
                <div x-show="currentTab === 'traceability'" x-transition>
                    @livewire('product-traceability')
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>