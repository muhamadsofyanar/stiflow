<?php

namespace App\Livewire;

use App\Models\PaymentProof;
use App\Services\Payment\PaymentVerificationService;
use Livewire\Component;

class PaymentVerifyCard extends Component
{
    public PaymentProof $proof;

    public string $note = '';

    public string $reason = '';

    public function approve(): void
    {
        try {
            app(PaymentVerificationService::class)->approvePayment($this->proof, auth()->user(), trim($this->note));
            $event = \App\Models\OutboxEvent::query()
                ->where('aggregate_type', \App\Models\Order::class)
                ->where('aggregate_id', $this->proof->paymentAttempt->order_id)
                ->where('event_type', 'fulfill_voucher')
                ->latest('id')
                ->first();
            if ($event) {
                \App\Jobs\ProcessVoucherFulfillmentJob::dispatch($event->id);
            }
            session()->flash('status', 'Pembayaran disetujui. Pemenuhan voucher berjalan di antrean.');
            $this->redirect(url()->previous(), navigate: false);
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
            $this->redirect(url()->previous(), navigate: false);
        }
    }

    public function reject(): void
    {
        if (trim($this->reason) === '') {
            session()->flash('error', 'Alasan penolakan wajib diisi.');
            $this->redirect(url()->previous(), navigate: false);
            return;
        }
        try {
            app(PaymentVerificationService::class)->rejectPayment($this->proof, auth()->user(), trim($this->reason));
            session()->flash('status', 'Bukti pembayaran ditolak.');
            $this->redirect(url()->previous(), navigate: false);
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
            $this->redirect(url()->previous(), navigate: false);
        }
    }

    public function render()
    {
        return view('livewire.payment-verify-card');
    }
}
