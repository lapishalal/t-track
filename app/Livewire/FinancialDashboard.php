<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Income;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialDashboard extends Component
{
    public $selectedShop = '';
    public $shops = [];
    
    // Properti Baru untuk Filter Waktu
    public $timeRange = 'all'; // Default: Semua Waktu
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->shops = Order::select('shop_name')->distinct()->pluck('shop_name')->toArray();
        if (!empty($this->shops)) {
            $this->selectedShop = $this->shops[0];
        }
        
        // Inisialisasi tanggal default (hari ini) untuk input kustom
        $this->startDate = Carbon::today()->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
    }

    /**
     * Helper untuk menerapkan filter tanggal ke query
     */
    private function applyDateFilter($query, $dateColumn = 'created_time')
    {
        switch ($this->timeRange) {
            case 'today':
                return $query->whereDate($dateColumn, Carbon::today());
            case 'yesterday':
                return $query->whereDate($dateColumn, Carbon::yesterday());
            case '7_days':
                return $query->where($dateColumn, '>=', Carbon::now()->subDays(7));
            case 'this_month':
                return $query->whereMonth($dateColumn, Carbon::now()->month)
                             ->whereYear($dateColumn, Carbon::now()->year);
            case 'custom':
                if ($this->startDate && $this->endDate) {
                    return $query->whereBetween($dateColumn, [
                        Carbon::parse($this->startDate)->startOfDay(),
                        Carbon::parse($this->endDate)->endOfDay()
                    ]);
                }
                return $query;
            default:
                return $query; // 'all'
        }
    }

    public function render()
    {
        // 1. Ambil Query Dasar Berdasarkan Filter Toko
        $orderQuery = Order::query()->when($this->selectedShop, fn($q) => $q->where('shop_name', $this->selectedShop));
        $incomeQuery = Income::query()->when($this->selectedShop, fn($q) => $q->where('shop_name', $this->selectedShop));

        // 2. TERAPKAN FILTER TANGGAL SECARA AMAN (Menggunakan Helper applyDateFilter)
        $orderQuery = $this->applyDateFilter($orderQuery, 'created_time');
        
        // Agar Dana Cair ikut terfilter sesuai waktu pencairan/waktu transaksinya di TikTok
        $incomeQuery = $this->applyDateFilter($incomeQuery, 'payout_time'); // sesuaikan dengan kolom tanggal di tabel incomes Anda (misal payout_time)

        // 3. Hitung Metrik Finansial Utama
        $totalOmsetKotor = $orderQuery->clone()->where('order_status', '!=', 'Cancelled')->sum('order_amount');
        $totalCairBersih = $incomeQuery->clone()->where('disbursement_amount', '>', 0)->sum('disbursement_amount');
        $totalBiayaAdmin = $incomeQuery->clone()->sum(DB::raw('platform_commission_fee + payment_fee'));

        // 4. Ambil Semua ID Pesanan yang SUDAH CAIR (Berdasarkan filter toko yang aktif)
        $allIncomeOrderIds = Income::query()
            ->when($this->selectedShop, fn($q) => $q->where('shop_name', $this->selectedShop))
            ->pluck('order_id')
            ->map(fn($id) => trim($id))
            ->toArray();

        // 5. Hitung TOTAL HPP & OVERHEAD (Hanya dari pesanan aktif hasil filter waktu)
        $totalHppDanOverhead = 0;

        // Tambahkan filter 'where' di sini sebelum mengambil data dari database
        $orders = $orderQuery->clone()->where('order_status', '!=', 'Cancelled')->get();

        foreach ($orders as $order) {
            $cost = \App\Models\ProductCost::where('sku_id', $order->sku_id)->first();

            if ($cost) {
                $totalHppDanOverhead += $order->quantity * ($cost->hpp_amount + $cost->overhead_per_pack);
            }
        }

        // 6. KALKULASI DANA BELUM CAIR (UNMATCHED ORDERS)
        $totalDanaMenggantung = 0;
        $pesananBelumCairList = collect();

        foreach ($orders as $order) {
            if ($order->order_status !== 'Cancelled') {
                // Jika ID Pesanan TIDAK ADA di dalam rumpun data Income, artinya BELUM CAIR
                if (!in_array(trim($order->order_id), $allIncomeOrderIds)) {
                    $totalDanaMenggantung += $order->order_amount;
                    $pesananBelumCairList->push($order);
                }
            }
        }

        // 7. HITUNG NET PROFIT BERSIH RIIL
        $profitBersihRiil = $totalCairBersih - $totalHppDanOverhead;

        // 8. Detektor Anomali Selisih Ongkir (Gunakan join TRIM yang sudah aman)
        $anomaliOngkir = Order::query()
            ->when($this->selectedShop, fn($q) => $q->where('orders.shop_name', $this->selectedShop))
            ->whereIn('orders.order_id', $orderQuery->clone()->pluck('order_id')->toArray())
            ->join('incomes', DB::raw('TRIM(orders.order_id)'), '=', DB::raw('TRIM(incomes.order_id)'))
            ->select(
                'orders.order_id',
                'orders.product_name',
                'orders.shipping_fee_estimated as estimasi',
                'incomes.shipping_fee_real as riil',
                DB::raw('(incomes.shipping_fee_real - orders.shipping_fee_estimated) as selisih_rugi'),
                DB::raw('(incomes.shipping_fee_real / NULLIF(orders.shipping_fee_estimated, 0)) as rasio_bengkak')
            )
            ->where('incomes.shipping_fee_real', '>', 0)
            ->whereRaw('incomes.shipping_fee_real > orders.shipping_fee_estimated')
            ->orderBy('selisih_rugi', 'desc')
            ->get();

        // 9. HITUNG KUANTITAS TERJUAL PER SKU (TRENDING PRODUCT)
        // Query ini otomatis mengikuti filter Toko dan Rentang Waktu dari $orderQuery
        $skuTrendingList = $orderQuery->clone()
            ->where('order_status', '!=', 'Cancelled')
            ->select(
                'sku_id',
                DB::raw('MAX(product_name) as nama_produk'), // Mengambil sampel nama produk agar mudah dibaca
                DB::raw('SUM(quantity) as total_terjual'),
                DB::raw('SUM(order_amount) as total_omset')
            )
            ->groupBy('sku_id')
            ->orderBy('total_terjual', 'desc') // Urutkan dari yang paling laris/trending
            ->get();

        return view('livewire.financial-dashboard', [
            'omsetKotor' => $totalOmsetKotor,
            'cairBersih' => $totalCairBersih,
            'biayaAdmin' => $totalBiayaAdmin,
            'hppOverhead' => $totalHppDanOverhead,
            'profitBersih' => $profitBersihRiil,
            'totalDanaMenggantung' => $totalDanaMenggantung,
            'pesananBelumCairList' => $pesananBelumCairList,
            'anomaliOngkirList' => $anomaliOngkir,
            'skuTrendingList' => $skuTrendingList
        ]);
    }
}