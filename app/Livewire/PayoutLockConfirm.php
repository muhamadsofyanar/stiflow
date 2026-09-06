<?php

namespace App\Livewire;

use App\Enums\PayoutStatus;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PayoutLockConfirm extends Component
{
    public Payout $payout;

    public bool $confirmLock = false;

    public string $notes = '';

    public function mount(Payout $payout): void
    {
        $this->payout = $payout;
    }

    public function confirmAndLock(): void
    {
        abort_if(! $this->confirmLock, 400, 'Silakan centang konfirmasi.');
        abort_if($this->payout->status !== PayoutStatus::Draft, 400, 'Payout bukan status Draft.');
        abort_if(! auth()->user()?->hasPermission('payouts.approve') && ! auth()->user()?->isAdmin(), 403);

        DB::transaction(function () {
            $this->payout->update([
                'status' => PayoutStatus::Locked,
                'locked_at' => now(),
                'locked_by_user_id' => auth()->id(),
                'admin_notes' => $this->notes ?: null,
            ]);
        });

        session()->flash('status', 'Payout berhasil dikunci. Siap untuk disetujui.');
        $this->redirect(route('admin.payouts.show', $this->payout), navigate: false);
    }

    public function render()
    {
        return view('livewire.payout-lock-confirm');
    }
}
