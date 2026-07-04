<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use App\Models\Income;
use App\Models\SalesTarget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialDashboard extends Component
{
    public $selectedShop = '';
    public $shops = [];
    public $timeRange = 'all';
    public $startDate;
    public $endDate;
    public $targetMonth;
    public $targetAmount = 0;

    // Shipping Claim
    public $showClaimModal = false;
    public $claimOrderId = '';
    public $claimTrackingId = '';
    public $claimSelisih = 0;
    public $claimEkspedisi = '';
    public $claimTicketNumber = '';
    public $claimStatus = 'belum_diklaim';
    public $claimSudahDiklaim = false;
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
        $this->targetMonth = Carbon::today()->format('Y-m');
        $this->loadTargetAmount();
    }

    public function updatedSelectedShop()
    {
        $this->loadTargetAmount();
    }

    public function updatedTargetMonth()
    {
        $this->loadTargetAmount();
    }

    public function loadTargetAmount()
    {
        if (!$this->selectedShop || !$this->targetMonth) {
            $this->targetAmount = 0;
            return;
        }

        $targetMonth = Carbon::createFromFormat('Y-m', $this->targetMonth)->startOfMonth()->toDateString();

        $this->targetAmount = (float) SalesTarget::where('shop_name', $this->selectedShop)
            ->whereDate('target_month', $targetMonth)
            ->value('target_amount');
    }

    public function saveMonthlyTarget()
    {
        abort_unless(auth()->user()?->isOwner(), 403);

        $this->validate([
            'selectedShop' => 'required|string',
            'targetMonth' => 'required|date_format:Y-m',
            'targetAmount' => 'required|numeric|min:0',
        ]);

        SalesTarget::updateOrCreate(
            [
                'shop_name' => $this->selectedShop,
                'target_month' => Carbon::createFromFormat('Y-m', $this->targetMonth)->startOfMonth()->toDateString(),
            ],
            [
                'target_amount' => $this->targetAmount,
                'created_by' => auth()->id(),
            ]
        );

        session()->flash('success_target', 'Target bulanan toko berhasil disimpan.');
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
        abort_unless(auth()->user()?->isOwner(), 403);

        $this->claimOrderId = $orderId;
        $this->claimTrackingId = $trackingId ?? '';
        $this->claimSelisih = $selisih;
        
        $existing = \App\Models\ShippingClaim::where('order_id', trim($orderId))->first();
        if ($existing) {
            $this->claimEkspedisi = $existing->ekspedisi;
            $this->claimTicketNumber = $existing->ticket_number;
            $this->claimStatus = $existing->status;
            $this->claimSudahDiklaim = in_array($existing->status, ['proses_klaim', 'berhasil'], true);
            $this->claimTanggalKlaim = $existing->tanggal_klaim ? $existing->tanggal_klaim->format('Y-m-d') : '';
            $this->claimKeterangan = $existing->keterangan;
        } else {
            $this->claimEkspedisi = '';
            $this->claimTicketNumber = '';
            $this->claimStatus = 'belum_diklaim';
            $this->claimSudahDiklaim = false;
            $this->claimTanggalKlaim = '';
            $this->claimKeterangan = '';
        }
        
        $this->showClaimModal = true;
    }

    public function closeClaimModal()
    {
        $this->showClaimModal = false;
        $this->reset(['claimOrderId', 'claimTrackingId', 'claimSelisih', 'claimEkspedisi', 'claimTicketNumber', 'claimStatus', 'claimSudahDiklaim', 'claimTanggalKlaim', 'claimKeterangan']);
    }

    public function updatedClaimSudahDiklaim($value)
    {
        if ($value && $this->claimStatus === 'belum_diklaim') {
            $this->claimStatus = 'proses_klaim';
        }

        if (!$value && in_array($this->claimStatus, ['proses_klaim', 'berhasil'], true)) {
            $this->claimStatus = 'belum_diklaim';
        }
    }

    public function updatedClaimStatus($value)
    {
        $this->claimSudahDiklaim = in_array($value, ['proses_klaim', 'berhasil'], true);
    }

    public function saveClaim()
    {
        abort_unless(auth()->user()?->isOwner(), 403);

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

    private function calculateHppOverhead($orders): float
    {
        $skuCosts = \App\Models\ProductCost::whereIn('sku_id', $orders->pluck('sku_id')->filter()->unique())
            ->get()
            ->keyBy('sku_id');

        return (float) $orders->sum(function ($order) use ($skuCosts) {
            $cost = $skuCosts->get($order->sku_id);

            return $cost ? $order->quantity * ($cost->hpp_amount + $cost->overhead_per_pack) : 0;
        });
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

        $returnRefundCandidates = $pesananBelumCairList
            ->map(function ($order) {
                $createdAt = $order->created_time ? Carbon::parse($order->created_time) : null;
                $order->days_pending = $createdAt ? (int) $createdAt->diffInDays(now()) : 0;
                $order->refund_risk = $order->days_pending >= 14 ? 'tinggi' : 'pantau';

                return $order;
            })
            ->filter(fn ($order) => $order->days_pending >= 7)
            ->sortByDesc('days_pending')
            ->values();

        $returnRefundHighRisk = $returnRefundCandidates->where('refund_risk', 'tinggi')->count();
        $returnRefundAmount = $returnRefundCandidates->sum('order_amount');

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

        $targetMonthStart = Carbon::createFromFormat('Y-m', $this->targetMonth ?: Carbon::today()->format('Y-m'))->startOfMonth();
        $targetMonthEnd = $targetMonthStart->copy()->endOfMonth();
        $targetSalesQuery = Order::query()
            ->where('order_status', '!=', 'Cancelled')
            ->whereBetween('created_time', [$targetMonthStart, $targetMonthEnd]);

        if ($this->selectedShop) {
            $targetSalesQuery->where('shop_name', $this->selectedShop);
            $monthlyTarget = (float) SalesTarget::where('shop_name', $this->selectedShop)
                ->whereDate('target_month', $targetMonthStart->toDateString())
                ->value('target_amount');
        } else {
            $monthlyTarget = (float) SalesTarget::whereDate('target_month', $targetMonthStart->toDateString())
                ->sum('target_amount');
        }

        $monthlySales = (float) $targetSalesQuery->sum('order_amount');
        $targetProgress = $monthlyTarget > 0 ? min(($monthlySales / $monthlyTarget) * 100, 100) : 0;
        $targetRemaining = max($monthlyTarget - $monthlySales, 0);

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
            $lastOrders = $lastOrderQuery->clone()->where('order_status', '!=', 'Cancelled')->get();
            $lastOrderCount = $lastOrders->count();
            $lastHppOverhead = $this->calculateHppOverhead($lastOrders);
            $lastProfit = $lastCair - $lastHppOverhead;
            $currentOrderCount = $orders->count();
            $comparisonMax = max($totalOmsetKotor, $lastOmset, abs($profitBersihRiil), abs($lastProfit), $currentOrderCount, $lastOrderCount, 1);

            $comparisonData = [
                'period_label' => $lastPeriodStart->format('d M') . ' - ' . $lastPeriodEnd->format('d M Y'),
                'omset' => $lastOmset,
                'cair' => $lastCair,
                'order_count' => $lastOrderCount,
                'profit' => $lastProfit,
                'current_order_count' => $currentOrderCount,
                'max_value' => $comparisonMax,
                'omset_delta' => $lastOmset > 0 ? (($totalOmsetKotor - $lastOmset) / $lastOmset) * 100 : 0,
                'cair_delta' => $lastCair > 0 ? (($totalCairBersih - $lastCair) / $lastCair) * 100 : 0,
                'profit_delta' => $lastProfit != 0 ? (($profitBersihRiil - $lastProfit) / abs($lastProfit)) * 100 : 0,
                'order_delta' => $lastOrderCount > 0 ? (($currentOrderCount - $lastOrderCount) / $lastOrderCount) * 100 : 0,
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
            'returnRefundCandidates' => $returnRefundCandidates,
            'returnRefundHighRisk' => $returnRefundHighRisk,
            'returnRefundAmount' => $returnRefundAmount,
            'anomaliOngkirList' => $anomaliOngkir,
            'skuTrendingList' => $skuTrendingList,
            'skuProfitList' => $skuProfitList,
            'provinceList' => $provinceList,
            'claimOrderIds' => $claimOrderIds,
            'claimsMap' => $claimsMap,
            'totalPotensiKlaim' => $totalPotensiKlaim,
            'totalSudahDiklaim' => $totalSudahDiklaim,
            'comparisonData' => $comparisonData,
            'monthlyTarget' => $monthlyTarget,
            'monthlySales' => $monthlySales,
            'targetProgress' => $targetProgress,
            'targetRemaining' => $targetRemaining,
            'targetMonthLabel' => $targetMonthStart->translatedFormat('F Y'),
            'isOwner' => auth()->user()?->isOwner() ?? false,
        ]);
    }
}
