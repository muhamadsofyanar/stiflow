<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class CheckoutPage extends Component
{
    public int $qty = 1;

    public ?Product $product = null;

    public function mount(): void
    {
        $this->product = Product::query()->voucher()->active()->with('voucherConfig')->latest()->first();
    }

    public function setQty(int $value): void
    {
        if (! $this->product || ! $this->product->voucherConfig) {
            return;
        }
        $cfg = $this->product->voucherConfig;
        $value = max($cfg->min_qty, min($cfg->max_qty, $value));
        $this->qty = $value;
    }

    public function getTotalProperty(): float
    {
        if (! $this->product || ! $this->product->voucherConfig) {
            return 0;
        }
        return (float) ($this->product->voucherConfig->unit_price * $this->qty);
    }

    public function getUnitPriceProperty(): float
    {
        return (float) ($this->product?->voucherConfig?->unit_price ?? 0);
    }

    public function getPresetsProperty(): array
    {
        return $this->product?->voucherConfig?->presets_json ?? [1, 5, 10, 25];
    }

    public function place(): void
    {
        $this->redirectRoute('promotor.checkout.place-order', [], navigate: false);
    }

    public function render()
    {
        return view('livewire.checkout-page', [
            'branch' => \App\Models\BranchSetting::current(),
            'profile' => auth()->user()?->promoterProfile,
        ]);
    }
}
