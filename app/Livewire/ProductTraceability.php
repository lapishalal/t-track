<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class ProductTraceability extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedMonth = '';

    // Filter Export Baru
    public $filterStatus = '';
    public $filterOrderDateType = '';
    public $filterOrderDateFrom = '';
    public $filterOrderDateTo = '';
    public $filterPayoutDateType = '';
    public $filterPayoutDateFrom = '';
    public $filterPayoutDateTo = '';

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->filterStatus = '';
        $this->filterOrderDateType = '';
        $this->filterOrderDateFrom = '';
        $this->filterOrderDateTo = '';
        $this->filterPayoutDateType = '';
        $this->filterPayoutDateFrom = '';
        $this->filterPayoutDateTo = '';
        $this->resetPage();
    }

    public $showOrderModal = false;
    public $selectedOrder = null;

    public function mount()
    {
        abort_unless(auth()->user()?->isOwner(), 403);
    }

    public function showOrderDetail($orderId)
    {
        $order = Order::with('income')->where('order_id', $orderId)->first();
        if ($order) {
            $cost = \App\Models\ProductCost::where('sku_id', $order->sku_id)->first();
            $order->hpp_amount = $cost ? $cost->hpp_amount : 0;
            $order->overhead_per_pack = $cost ? $cost->overhead_per_pack : 0;
        }
        $this->selectedOrder = $order;
        $this->showOrderModal = true;
    }

    public function closeOrderDetail()
    {
        $this->showOrderModal = false;
        $this->selectedOrder = null;
    }

    /**
     * Fitur Clean Report Export (.xlsx)
     */
    public function exportCleanReport()
    {
        $orders = Order::query()
            ->with('income')
            ->join('product_costs', 'orders.sku_id', '=', 'product_costs.sku_id')
            ->select('orders.*', 'product_costs.hpp_amount', 'product_costs.overhead_per_pack')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap T-Track Matang');

        // Header Tabel Excel
        $headers = ['Order ID', 'SKU ID', 'Nama Toko', 'Nama Produk', 'Status Pesanan', 'Qty', 'Omset Kotor', 'Dana Cair', 'HPP', 'Overhead', 'Profit Bersih'];
        $sheet->fromArray($headers, NULL, 'A1');

        $rowNum = 2;
        foreach ($orders as $order) {
            $cair = $order->income ? $order->income->disbursement_amount : 0;
            $hppTotal = $order->quantity * $order->hpp_amount;
            $overheadTotal = $order->quantity * $order->overhead_per_pack;
            $profit = $cair - ($hppTotal + $overheadTotal);

            $sheet->setCellValue('A' . $rowNum, $order->order_id);
            $sheet->setCellValue('B' . $rowNum, $order->sku_id);
            $sheet->setCellValue('C' . $rowNum, $order->shop_name);
            $sheet->setCellValue('D' . $rowNum, $order->product_name);
            $sheet->setCellValue('E' . $rowNum, $order->order_status);
            $sheet->setCellValue('F' . $rowNum, $order->quantity);
            $sheet->setCellValue('G' . $rowNum, $order->order_amount);
            $sheet->setCellValue('H' . $rowNum, $cair);
            $sheet->setCellValue('I' . $rowNum, $hppTotal);
            $sheet->setCellValue('J' . $rowNum, $overheadTotal);
            $sheet->setCellValue('K' . $rowNum, $profit);
            $rowNum++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'T-Track_Clean_Report_' . date('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/public/' . $fileName);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Fitur Export dengan Filter (.xlsx)
     */
    public function exportFilteredReport()
    {
        $query = Order::query()
            ->with('income')
            ->join('product_costs', 'orders.sku_id', '=', 'product_costs.sku_id')
            ->select('orders.*', 'product_costs.hpp_amount', 'product_costs.overhead_per_pack');

        // Filter status pesanan
        if ($this->filterStatus) {
            $query->where('orders.order_status', $this->filterStatus);
        }

        // Filter tanggal pesanan
        if ($this->filterOrderDateType) {
            $this->applyDateFilter($query, 'orders.created_time', $this->filterOrderDateType, $this->filterOrderDateFrom, $this->filterOrderDateTo);
        }

        // Filter tanggal cair
        if ($this->filterPayoutDateType) {
            $query->whereHas('income', function($q) {
                $this->applyDateFilter($q, 'payout_time', $this->filterPayoutDateType, $this->filterPayoutDateFrom, $this->filterPayoutDateTo);
            });
        }

        $orders = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap T-Track Filtered');

        // Header Tabel Excel
        $headers = ['Order ID', 'SKU ID', 'Nama Toko', 'Nama Produk', 'Status Pesanan', 'Qty', 'Omset Kotor', 'Dana Cair', 'HPP', 'Overhead', 'Profit Bersih', 'Tanggal Pesanan', 'Tanggal Cair'];
        $sheet->fromArray($headers, NULL, 'A1');

        $rowNum = 2;
        foreach ($orders as $order) {
            $cair = $order->income ? $order->income->disbursement_amount : 0;
            $hppTotal = $order->quantity * $order->hpp_amount;
            $overheadTotal = $order->quantity * $order->overhead_per_pack;
            $profit = $cair - ($hppTotal + $overheadTotal);

            $sheet->setCellValue('A' . $rowNum, $order->order_id);
            $sheet->setCellValue('B' . $rowNum, $order->sku_id);
            $sheet->setCellValue('C' . $rowNum, $order->shop_name);
            $sheet->setCellValue('D' . $rowNum, $order->product_name);
            $sheet->setCellValue('E' . $rowNum, $order->order_status);
            $sheet->setCellValue('F' . $rowNum, $order->quantity);
            $sheet->setCellValue('G' . $rowNum, $order->order_amount);
            $sheet->setCellValue('H' . $rowNum, $cair);
            $sheet->setCellValue('I' . $rowNum, $hppTotal);
            $sheet->setCellValue('J' . $rowNum, $overheadTotal);
            $sheet->setCellValue('K' . $rowNum, $profit);
            $sheet->setCellValue('L' . $rowNum, $order->created_time ? Carbon::parse($order->created_time)->format('Y-m-d H:i:s') : '-');
            $sheet->setCellValue('M' . $rowNum, ($order->income && $order->income->payout_time) ? Carbon::parse($order->income->payout_time)->format('Y-m-d H:i:s') : '-');
            $rowNum++;
        }

        // Auto-size columns
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'T-Track_Filtered_Report_' . date('Ymd_His') . '.xlsx';
        $filePath = storage_path('app/public/' . $fileName);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    private function applyDateFilter($query, $column, $type, $from, $to)
    {
        $now = Carbon::now();

        switch ($type) {
            case 'today':
                $query->whereDate($column, $now->toDateString());
                break;
            case 'this_week':
                $query->whereBetween($column, [$now->copy()->startOfWeek()->toDateTimeString(), $now->copy()->endOfWeek()->toDateTimeString()]);
                break;
            case 'this_month':
                $query->whereMonth($column, $now->month)->whereYear($column, $now->year);
                break;
            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $query->whereMonth($column, $lastMonth->month)->whereYear($column, $lastMonth->year);
                break;
            case 'custom':
                if ($from) {
                    $query->whereDate($column, '>=', $from);
                }
                if ($to) {
                    $query->whereDate($column, '<=', $to);
                }
                break;
        }
    }

    public function render()
    {
        $ordersData = Order::query()
            ->with('income')
            ->where(function($q) {
                $q->where('product_name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku_id', 'like', '%' . $this->search . '%')
                  ->orWhere('tracking_id', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedMonth, function($q) {
                return $q->whereMonth('created_time', $this->selectedMonth);
            })
            ->when($this->filterStatus, function($q) {
                return $q->where('order_status', $this->filterStatus);
            })
            ->when($this->filterOrderDateType, function($q) {
                $this->applyDateFilter($q, 'created_time', $this->filterOrderDateType, $this->filterOrderDateFrom, $this->filterOrderDateTo);
            })
            ->when($this->filterPayoutDateType, function($q) {
                $q->whereHas('income', function($incomeQ) {
                    $this->applyDateFilter($incomeQ, 'payout_time', $this->filterPayoutDateType, $this->filterPayoutDateFrom, $this->filterPayoutDateTo);
                });
            })
            ->orderBy('created_time', 'desc')
            ->paginate(10);

        $statuses = Order::select('order_status')->distinct()->orderBy('order_status')->pluck('order_status');

        return view('livewire.product-traceability', [
            'ordersData' => $ordersData,
            'statuses' => $statuses
        ]);
    }
}
