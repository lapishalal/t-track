<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProductCost;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ProductCostManager extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'sku_id';
    public $sortDirection = 'asc';

    public $editingSku = null;
    public $hpp_amount = 0;
    public $overhead_per_pack = 0;

    public $showHistoryModal = false;
    public $historySku = null;
    public $historyRecords = [];

    public function mount()
    {
        abort_unless(auth()->user()?->isOwner(), 403);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function startEdit($skuId, $currentHpp, $currentOverhead)
    {
        $this->editingSku = $skuId;
        $this->hpp_amount = $currentHpp;
        $this->overhead_per_pack = $currentOverhead;
    }

    public function saveEdit()
    {
        $this->validate([
            'hpp_amount' => 'required|numeric|min:0',
            'overhead_per_pack' => 'required|numeric|min:0',
        ]);

        if ($this->editingSku) {
            $product = ProductCost::where('sku_id', $this->editingSku)->first();
            if ($product) {
                \App\Models\ProductCostHistory::create([
                    'sku_id' => $product->sku_id,
                    'shop_name' => $product->shop_name,
                    'product_name' => $product->product_name,
                    'hpp_amount_old' => $product->hpp_amount,
                    'hpp_amount_new' => $this->hpp_amount,
                    'overhead_per_pack_old' => $product->overhead_per_pack,
                    'overhead_per_pack_new' => $this->overhead_per_pack,
                    'changed_by' => auth()->user()->name ?? 'System',
                ]);

                $product->update([
                    'hpp_amount' => $this->hpp_amount,
                    'overhead_per_pack' => $this->overhead_per_pack,
                ]);
            }
        }

        session()->flash('success_cost', "Data SKU {$this->editingSku} berhasil diperbarui.");
        $this->editingSku = null;
    }

    public function showHistory($skuId)
    {
        $this->historySku = $skuId;
        $this->historyRecords = \App\Models\ProductCostHistory::where('sku_id', $skuId)
            ->orderBy('created_at', 'desc')
            ->get();
        $this->showHistoryModal = true;
    }

    public function closeHistory()
    {
        $this->showHistoryModal = false;
        $this->historySku = null;
        $this->historyRecords = [];
    }

    public function cancelEdit()
    {
        $this->editingSku = null;
    }

    public function render()
    {
        $products = ProductCost::query()
            ->where(function($query) {
                $query->where('sku_id', 'like', '%' . $this->search . '%')
                      ->orWhere('product_name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        // Slow-Moving / Dead Stock Alert
        $deadStockThreshold = now()->subDays(30);
        $slowMovingSkus = \App\Models\Order::select('sku_id', 'product_name', DB::raw('MAX(created_time) as last_order_date'), DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('sku_id', 'product_name')
            ->havingRaw('MAX(created_time) < ?', [$deadStockThreshold])
            ->orderBy('last_order_date', 'asc')
            ->limit(20)
            ->get();

        $allOrderSkus = \App\Models\Order::select('sku_id')->distinct()->pluck('sku_id')->toArray();
        $neverSoldSkus = ProductCost::whereNotIn('sku_id', $allOrderSkus)
            ->select('sku_id', 'product_name', 'hpp_amount')
            ->limit(20)
            ->get();

        return view('livewire.product-cost-manager', [
            'products' => $products,
            'slowMovingSkus' => $slowMovingSkus,
            'neverSoldSkus' => $neverSoldSkus,
        ]);
    }
}
