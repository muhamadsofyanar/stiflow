<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PayoutStatus;
use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutAdminController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $query = Payout::query()->with(['items', 'promoterProfile.user'])->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $payouts = $query->paginate(20);

        return view('admin.payouts.index', compact('payouts'));
    }

    public function show(Payout $payout): View
    {
        $payout->load(['items.commissionEntry', 'promoterProfile.user']);

        return view('admin.payouts.show', compact('payout'));
    }

    public function lock(Request $request, Payout $payout): RedirectResponse
    {
        abort_if($payout->status !== PayoutStatus::Draft, 400, 'Payout hanya bisa dikunci saat status Draft.');

        $payout->update([
            'status' => PayoutStatus::Locked,
            'locked_at' => now(),
            'locked_by_user_id' => auth()->id(),
        ]);

        return back()->with('status', 'Payout dikunci. Menunggu persetujuan.');
    }

    public function approve(Request $request, Payout $payout): RedirectResponse
    {
        abort_if($payout->status !== PayoutStatus::Locked, 400, 'Payout harus dikunci sebelum disetujui.');

        DB::transaction(function () use ($payout) {
            $payout->update([
                'status' => PayoutStatus::Approved,
                'approved_at' => now(),
                'approved_by_user_id' => auth()->id(),
            ]);
        });

        return back()->with('status', 'Payout disetujui.');
    }
}
