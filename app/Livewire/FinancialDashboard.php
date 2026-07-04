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
    public $timeRange = 'all';
    public $startDate;
    public $endDate;

    // Shipping Claim
    public $showClaimModal = false;
    public $claimOrderId = '';
    public $claimTrackingId = '';
    public $claimSelisih = 0;
    public $claimEkspedisi = '';
    public $claimTicketNumber = '';
    public $claimStatus = 'belum_diklaim';
    public $claimTanggalKlaim = '';
    public $claimKeterangan = '';

    // MoM Comparison
    public $showComparison = false;

    public function mount()
    {
        $this->shops = Order::select('shop_name')->distinct()->pluck('shop_name')->toArray();
        if (!empty($this->shops)) {
            $this->selectedShop = $this->shops[0];
        }
        $this->startDate = Carbon::today()->format('Y-m-d');
        $this->endDate = Carbon::today()->format('Y-m-d');
    }

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
                return $query;
        }
    }

    public function openClaimModal($orderId, $trackingId, $selisih)
    {
        $this->claimOrderId = $orderId;
        $this->claimTrackingId = $trackingId ?? '';
        $this->claimSelisih = $selisih;
        
        $existing = \App\Models\ShippingClaim::where('order_id', trim($orderId))->first();
        if ($existing) {
            $this->claimEkspedisi = $existing->ekspedisi;
            $this->claimTicketNumber = $existing->ticket_number;
            $this->claimStatus = $existing->status;
            $this->claimTanggalKlaim = $existing->tanggal_klaim ? $existing->tanggal_klaim->format('Y-m-d') : '';
            $this->claimKeterangan = $existing->keterangan;
        } else {
            $this->claimEkspedisi = '';
            $this->claimTicketNumber = '';
            $this->claimStatus = 'belum_diklaim';
            $this->claimTanggalKlaim = '';
            $this->claimKeterangan = '';
        }
        
        $this->showClaimModal = true;
    }

    public function closeClaimModal()
    {
        $this->showClaimModal = false;
        $this->reset(['claimOrderId', 'claimTrackingId', 'claimSelisih', 'claimEkspedisi', 'claimTicketNumber', 'claimStatus', 'claimTanggalKlaim', 'claimKeterangan']);
    }

    public function saveClaim()
    {
        $this->validate([
            'claimOrderId' => 'required|string',
            'claimEkspedisi' => 'nullable|string|max:50',
            'claimTicketNumber' => 'nullable|string|max:100',
            'claimStatus' => 'required|in:belum_diklaim,proses_klaim,berhasil,ditolak',
            'claimTanggalKlaim' => 'nullable|date',
            'claimKeterangan' => 'nullable|string|max:500',
        ]);

        \App\Models\ShippingClaim::updateOrCreate(
            ['order_id' => trim($this->claimOrderId)],
            [
                'tracking_id' => $this->claimTrackingId,
                'selisih_rugi' => $this->claimSelisih,
                'ekspedisi' => $this->claimEkspedisi,
                'ticket_number' => $this->claimTicketNumber,
                'status' => $this->claimStatus,
                'tanggal_klaim' => $this->claimTanggalKlaim ?: null,
                'keterangan' => $this->claimKeterangan,
                'created_by' => auth()->user()->name ?? 'System',
            ]
        );

        $this->closeClaimModal();
        session()->flash('success_claim', 'Data klaim ongkir berhasil disimpan.');
    }

    public function toggleComparison()
    {
        $this->showComparison = !$this->showComparison;
    }

    public function render()
    {
        $orderQuery = Order::query()->when($this->selectedShop, fn($q) => $q->where('shop_name', $this->selectedShop));
        $incomeQuery = Income::query()->when($this->selectedShop, fn($q) => $q->where('shop_name', $this->selectedShop));

        $orderQuery = $this->applyDateFilter($orderQuery, 'created_time');
        $incomeQuery = $this->applyDateFilter($incomeQuery, 'payout_time');

        $totalOmsetKotor = $orderQuery->clone()->where('order_status', '!=', 'Cancelled')->sum('order_amount');
        $totalCairBersih = $incomeQuery->clone()->where('disbursement_amount', '>', 0)->sum('disbursement_amount');
        $totalBiayaAdmin = $incomeQuery->clone()->sum(DB::raw('platform_commission_fee + payment_fee'));

        $allIncomeOrderIds = Income::query()
            ->when($this->selectedShop, fn($q) => $q->where('shop_name', $this->selectedShop))
            ->pluck('order_id')
            ->map(fn($id) => trim($id))
            ->toArray();

        $incomeMap = Income::query()
            ->when($this->selectedShop, fn($q) => $q->where('shop_name', $this->selectedShop))
            ->get()
            ->keyBy(fn($income) => trim($income->order_id));

        $totalHppDanOverhead = 0;
        $orders = $orderQuery->clone()->where('order_status', '!=', 'Cancelled')->with('income')->get();

        foreach ($orders as $order) {
            $cost = \App\Models\ProductCost::where('sku_id', $order->sku_id)->first();
            if ($cost) {
                $totalHppDanOverhead += $order->quantity * ($cost->hpp_amount + $cost->overhead_per_pack);
            }
        }

        $totalDanaMenggantung = 0;
        $pesananBelumCairList = collect();
        foreach ($orders as $order) {
            if ($order->order_status !== 'Cancelled' && !in_array(trim($order->order_id), $allIncomeOrderIds)) {
                $totalDanaMenggantung += $order->order_amount;
                $pesananBelumCairList->push($order);
            }
        }

        $profitBersihRiil = $totalCairBersih - $totalHppDanOverhead;

        // Profit Breakdown per SKU
        $skuProfitMap = [];
        foreach ($orders as $order) {
            $cost = \App\Models\ProductCost::where('sku_id', $order->sku_id)->first();
            $trimmedOrderId = trim($order->order_id);
            $incomeRecord = $incomeMap->get($trimmedOrderId);
            $cair = $incomeRecord ? $incomeRecord->disbursement_amount : 0;
            $hpp = $cost ? $order->quantity * $cost->hpp_amount : 0;
            $overhead = $cost ? $order->quantity * $cost->overhead_per_pack : 0;
            $profit = $cair - ($hpp + $overhead);

            if (!isset($skuProfitMap[$order->sku_id])) {
                $skuProfitMap[$order->sku_id] = [
                    'sku_id' => $order->sku_id,
                    'product_name' => $order->product_name,
                    'total_sold' => 0, 'total_omset' => 0, 'total_cair' => 0,
                    'total_hpp' => 0, 'total_overhead' => 0, 'total_profit' => 0,
                ];
            }
            $skuProfitMap[$order->sku_id]['total_sold'] += $order->quantity;
            $skuProfitMap[$order->sku_id]['total_omset'] += $order->order_amount;
            $skuProfitMap[$order->sku_id]['total_cair'] += $cair;
            $skuProfitMap[$order->sku_id]['total_hpp'] += $hpp;
            $skuProfitMap[$order->sku_id]['total_overhead'] += $overhead;
            $skuProfitMap[$order->sku_id]['total_profit'] += $profit;
        }
        $skuProfitList = collect($skuProfitMap)->sortByDesc('total_profit')->values();

        // Regional Dashboard
        $provinceMap = [];
        foreach ($orders as $order) {
            $province = $order->province ?? 'Tidak Diketahui';
            if (!isset($provinceMap[$province])) {
                $provinceMap[$province] = [
                    'province' => $province, 'total_orders' => 0, 'total_omset' => 0,
                    'total_shipping' => 0, 'pending_orders' => 0, 'pending_amount' => 0,
                ];
            }
            $provinceMap[$province]['total_orders']++;
            $provinceMap[$province]['total_omset'] += $order->order_amount;
            $provinceMap[$province]['total_shipping'] += $order->shipping_fee_estimated ?? 0;
            if (!in_array(trim($order->order_id), $allIncomeOrderIds)) {
                $provinceMap[$province]['pending_orders']++;
                $provinceMap[$province]['pending_amount'] += $order->order_amount;
            }
        }
        $provinceList = collect($provinceMap)
            ->map(function($item) {
                $item['avg_shipping'] = $item['total_orders'] > 0 ? $item['total_shipping'] / $item['total_orders'] : 0;
                return $item;
            })
            ->sortByDesc('total_orders')
            ->values();

        // Anomali Ongkir
        $anomaliOngkir = Order::query()
            ->when($this->selectedShop, fn($q) => $q->where('orders.shop_name', $this->selectedShop))
            ->whereIn('orders.order_id', $orderQuery->clone()->pluck('order_id')->toArray())
            ->join('incomes', DB::raw('TRIM(orders.order_id)'), '=', DB::raw('TRIM(incomes.order_id)'))
            ->select(
                'orders.order_id', 'orders.product_name', 'orders.tracking_id',
                'orders.shipping_fee_estimated as estimasi',
                'incomes.shipping_fee_real as riil',
                DB::raw('(incomes.shipping_fee_real - orders.shipping_fee_estimated) as selisih_rugi'),
                DB::raw('(incomes.shipping_fee_real / NULLIF(orders.shipping_fee_estimated, 0)) as rasio_bengkak')
            )
            ->where('incomes.shipping_fee_real', '>', 0)
            ->whereRaw('incomes.shipping_fee_real > orders.shipping_fee_estimated')
            ->orderBy('selisih_rugi', 'desc')
            ->get();

        $claimOrderIds = \App\Models\ShippingClaim::pluck('order_id')->map(fn($id) => trim($id))->toArray();
        $claimsMap = \App\Models\ShippingClaim::all()->keyBy(fn($c) => trim($c->order_id));

        $totalPotensiKlaim = $anomaliOngkir->sum('selisih_rugi');
        $totalSudahDiklaim = 0;
        foreach ($anomaliOngkir as $a) {
            $cid = trim($a->order_id);
            if (isset($claimsMap[$cid]) && in_array($claimsMap[$cid]->status, ['berhasil', 'proses_klaim'])) {
                $totalSudahDiklaim += $a->selisih_rugi;
            }
        }

        // Trending SKU
        $skuTrendingList = $orderQuery->clone()
            ->where('order_status', '!=', 'Cancelled')
            ->select(
                'sku_id',
                DB::raw('MAX(product_name) as nama_produk'),
                DB::raw('SUM(quantity) as total_terjual'),
                DB::raw('SUM(order_amount) as total_omset')
            )
            ->groupBy('sku_id')
            ->orderBy('total_terjual', 'desc')
            ->get();

        // MoM Comparison
        $comparisonData = null;
        if ($this->showComparison && $this->timeRange !== 'custom') {
            $now = Carbon::now();
            if ($this->timeRange === 'this_month') {
                $lastPeriodStart = $now->copy()->subMonth()->startOfMonth();
                $lastPeriodEnd = $now->copy()->subMonth()->endOfMonth();
            } elseif ($this->timeRange === '7_days') {
                $lastPeriodStart = $now->copy()->subDays(14);
                $lastPeriodEnd = $now->copy()->subDays(7);
            } elseif ($this->timeRange === 'today') {
                $lastPeriodStart = $now->copy()->yesterday()->startOfDay();
                $lastPeriodEnd = $now->copy()->yesterday()->endOfDay();
            } else {
                $lastPeriodStart = $now->copy()->subMonth()->startOfMonth();
                $lastPeriodEnd = $now->copy()->subMonth()->endOfMonth();
            }

            $lastOrderQuery = Order::query()
                ->when($this->selectedShop, fn($q) => $q->where('shop_name', $this->selectedShop))
                ->whereBetween('created_time', [$lastPeriodStart, $lastPeriodEnd]);
            
            $lastIncomeQuery = Income::query()
                ->when($this->selectedShop, fn($q) => $q->where('shop_name', $this->selectedShop))
                ->whereBetween('payout_time', [$lastPeriodStart, $lastPeriodEnd]);

            $lastOmset = $lastOrderQuery->clone()->where('order_status', '!=', 'Cancelled')->sum('order_amount');
            $lastCair = $lastIncomeQuery->clone()->where('disbursement_amount', '>', 0)->sum('disbursement_amount');
            $lastOrderCount = $lastOrderQuery->clone()->where('order_status', '!=', 'Cancelled')->count();

            $comparisonData = [
                'period_label' => $lastPeriodStart->format('d M') . ' - ' . $lastPeriodEnd->format('d M Y'),
                'omset' => $lastOmset,
                'cair' => $lastCair,
                'order_count' => $lastOrderCount,
                'omset_delta' => $lastOmset > 0 ? (($totalOmsetKotor - $lastOmset) / $lastOmset) * 100 : 0,
                'cair_delta' => $lastCair > 0 ? (($totalCairBersih - $lastCair) / $lastCair) * 100 : 0,
            ];
        }

        return view('livewire.financial-dashboard', [
            'omsetKotor' => $totalOmsetKotor,
            'cairBersih' => $totalCairBersih,
            'biayaAdmin' => $totalBiayaAdmin,
            'hppOverhead' => $totalHppDanOverhead,
            'profitBersih' => $profitBersihRiil,
            'totalDanaMenggantung' => $totalDanaMenggantung,
            'pesananBelumCairList' => $pesananBelumCairList,
            'anomaliOngkirList' => $anomaliOngkir,
            'skuTrendingList' => $skuTrendingList,
            'skuProfitList' => $skuProfitList,
            'provinceList' => $provinceList,
            'claimOrderIds' => $claimOrderIds,
            'claimsMap' => $claimsMap,
            'totalPotensiKlaim' => $totalPotensiKlaim,
            'totalSudahDiklaim' => $totalSudahDiklaim,
            'comparisonData' => $comparisonData,
        ]);
    }
}
