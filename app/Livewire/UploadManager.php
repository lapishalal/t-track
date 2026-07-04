<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\TikTokParserService;

class UploadManager extends Component
{
    use WithFileUploads;

    public $shop_name;
    public $file_order;
    public $file_income;

    public function processOrder(TikTokParserService $parser)
    {
        $this->validate([
            'shop_name' => 'required|string|min:3|max:255',
            'file_order' => 'required|file|extensions:xlsx,xls,csv|max:10240',
        ]);

        try {
            $this->shop_name = trim($this->shop_name);
            $filePath = $this->file_order->getRealPath();
            $originalName = $this->file_order->getClientOriginalName();

            $rows = $parser->parseOrderCsv($filePath, $this->shop_name, $originalName);

            session()->flash('success_order', "Berhasil mengimpor {$rows} data pesanan untuk toko: {$this->shop_name}");
            $this->reset('file_order');
        } catch (\Exception $e) {
            session()->flash('error_order', $e->getMessage());
        }
    }

    public function processIncome(TikTokParserService $parser)
    {
        $this->validate([
            'shop_name' => 'required|string|min:3|max:255',
            'file_income' => 'required|file|extensions:xlsx,xls,csv|max:10240',
        ]);

        try {
            $this->shop_name = trim($this->shop_name);
            $filePath = $this->file_income->getRealPath();
            $originalName = $this->file_income->getClientOriginalName();

            $rows = $parser->parseIncomeXlsx($filePath, $this->shop_name, $originalName);

            session()->flash('success_income', "Berhasil mengimpor {$rows} data keuangan (income) untuk toko: {$this->shop_name}");
            $this->reset('file_income');
        } catch (\Exception $e) {
            session()->flash('error_income', $e->getMessage());
        }
    }

    public function render()
    {
        $uploadLogs = \App\Models\UploadLog::orderBy('created_at', 'desc')->limit(50)->get();
        return view('livewire.upload-manager', ['uploadLogs' => $uploadLogs]);
    }
}
