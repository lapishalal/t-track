<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProductCost;
use Livewire\WithPagination;

class ProductCostManager extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'sku_id'; // Field default untuk pengurutan
    public $sortDirection = 'asc'; // Arah default

    public $editingSku = null;
    public $hpp_amount = 0;
    public $overhead_per_pack = 0;

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
                $product->update([
                    'hpp_amount' => $this->hpp_amount,
                    'overhead_per_pack' => $this->overhead_per_pack,
                ]);
            }
        }

        session()->flash('success_cost', "Data SKU {$this->editingSku} berhasil diperbarui.");
        $this->editingSku = null;
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

        return view('livewire.product-cost-manager', compact('products'));
    }
}