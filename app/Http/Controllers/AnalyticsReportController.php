<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Order;
use App\Models\ProductCost;
use App\Models\SalesTarget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $selectedShop = (string) $request->query('shop', '');
        $timeRange = (string) $request->query('range', 'all');
        $startDate = $request->query('start');
        $endDate = $request->query('end');
        $targetMonth = $request->query('target_month', Carbon::today()->format('Y-m'));

        $orderQuery = Order::query()->when($selectedShop, fn ($q) => $q->where('shop_name', $selectedShop));
        $incomeQuery = Income::query()->when($selectedShop, fn ($q) => $q->where('shop_name', $selectedShop));

        $this->applyDateFilter($orderQuery, 'created_time', $timeRange, $startDate, $endDate);
        $this->applyDateFilter($incomeQuery, 'payout_time', $timeRange, $startDate, $endDate);

        $orders = $orderQuery->clone()
            ->where('order_status', '!=', 'Cancelled')
            ->with('income')
            ->get();

        $incomeMap = Income::query()
            ->when($selectedShop, fn ($q) => $q->where('shop_name', $selectedShop))
            ->get()
            ->keyBy(fn ($income) => trim($income->order_id));

        $totalOmsetKotor = (float) $orders->sum('order_amount');
        $totalCairBersih = (float) $incomeQuery->clone()->where('disbursement_amount', '>', 0)->sum('disbursement_amount');
        $totalBiayaAdmin = (float) $incomeQuery->clone()->sum(DB::raw('platform_commission_fee + payment_fee'));

        $totalHppDanOverhead = 0;
        $skuProfitMap = [];
        foreach ($orders as $order) {
            $cost = ProductCost::where('sku_id', $order->sku_id)->first();
            $incomeRecord = $incomeMap->get(trim($order->order_id));
            $cair = $incomeRecord ? (float) $incomeRecord->disbursement_amount : 0;
            $hpp = $cost ? $order->quantity * (float) $cost->hpp_amount : 0;
            $overhead = $cost ? $order->quantity * (float) $cost->overhead_per_pack : 0;
            $profit = $cair - ($hpp + $overhead);

            $totalHppDanOverhead += $hpp + $overhead;

            if (!isset($skuProfitMap[$order->sku_id])) {
                $skuProfitMap[$order->sku_id] = [
                    'sku_id' => $order->sku_id,
                    'product_name' => $order->product_name,
                    'total_sold' => 0,
                    'total_omset' => 0,
                    'total_cair' => 0,
                    'total_profit' => 0,
                ];
            }

            $skuProfitMap[$order->sku_id]['total_sold'] += $order->quantity;
            $skuProfitMap[$order->sku_id]['total_omset'] += (float) $order->order_amount;
            $skuProfitMap[$order->sku_id]['total_cair'] += $cair;
            $skuProfitMap[$order->sku_id]['total_profit'] += $profit;
        }

        $skuProfitList = collect($skuProfitMap)->sortByDesc('total_profit')->take(12)->values();
        $topSkuList = collect($skuProfitMap)->sortByDesc('total_sold')->take(8)->values();
        $maxTopSkuSold = max((int) $topSkuList->max('total_sold'), 1);

        $targetMonthStart = Carbon::createFromFormat('Y-m', $targetMonth)->startOfMonth();
        $targetMonthEnd = $targetMonthStart->copy()->endOfMonth();
        $targetSalesQuery = Order::query()
            ->where('order_status', '!=', 'Cancelled')
            ->whereBetween('created_time', [$targetMonthStart, $targetMonthEnd]);

        if ($selectedShop) {
            $targetSalesQuery->where('shop_name', $selectedShop);
            $monthlyTarget = (float) SalesTarget::where('shop_name', $selectedShop)
                ->whereDate('target_month', $targetMonthStart->toDateString())
                ->value('target_amount');
        } else {
            $monthlyTarget = (float) SalesTarget::whereDate('target_month', $targetMonthStart->toDateString())
                ->sum('target_amount');
        }

        $monthlySales = (float) $targetSalesQuery->sum('order_amount');
        $targetProgress = $monthlyTarget > 0 ? min(($monthlySales / $monthlyTarget) * 100, 100) : 0;

        return view('reports.analytics-print', [
            'selectedShop' => $selectedShop ?: 'Semua Toko',
            'periodLabel' => $this->periodLabel($timeRange, $startDate, $endDate),
            'generatedAt' => Carbon::now()->format('d M Y H:i'),
            'omsetKotor' => $totalOmsetKotor,
            'cairBersih' => $totalCairBersih,
            'biayaAdmin' => $totalBiayaAdmin,
            'hppOverhead' => $totalHppDanOverhead,
            'profitBersih' => $totalCairBersih - $totalHppDanOverhead,
            'orderCount' => $orders->count(),
            'skuProfitList' => $skuProfitList,
            'topSkuList' => $topSkuList,
            'maxTopSkuSold' => $maxTopSkuSold,
            'targetMonthLabel' => $targetMonthStart->translatedFormat('F Y'),
            'monthlyTarget' => $monthlyTarget,
            'monthlySales' => $monthlySales,
            'targetProgress' => $targetProgress,
        ]);
    }

    private function applyDateFilter($query, string $dateColumn, string $timeRange, $startDate, $endDate): void
    {
        match ($timeRange) {
            'today' => $query->whereDate($dateColumn, Carbon::today()),
            'yesterday' => $query->whereDate($dateColumn, Carbon::yesterday()),
            '7_days' => $query->where($dateColumn, '>=', Carbon::now()->subDays(7)),
            'this_month' => $query->whereMonth($dateColumn, Carbon::now()->month)->whereYear($dateColumn, Carbon::now()->year),
            'custom' => $startDate && $endDate
                ? $query->whereBetween($dateColumn, [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()])
                : null,
            default => null,
        };
    }

    private function periodLabel(string $timeRange, $startDate, $endDate): string
    {
        return match ($timeRange) {
            'today' => 'Hari Ini',
            'yesterday' => 'Kemarin',
            '7_days' => '7 Hari Terakhir',
            'this_month' => 'Bulan Ini',
            'custom' => $startDate && $endDate
                ? Carbon::parse($startDate)->format('d M Y') . ' - ' . Carbon::parse($endDate)->format('d M Y')
                : 'Tanggal Kustom',
            default => 'Semua Waktu',
        };
    }
}
