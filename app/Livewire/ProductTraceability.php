<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductTraceability extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedMonth = '';

    public function updatingSearch()
    {
        $this->resetPage();
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
            ->orderBy('created_time', 'desc')
            ->paginate(10);

        return view('livewire.product-traceability', [
            'ordersData' => $ordersData
        ]);
    }
}