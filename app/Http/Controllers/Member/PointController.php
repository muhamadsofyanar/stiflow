<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\PointLedgerEntry;
use Illuminate\Contracts\View\View;

class PointController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $hasLinkedContact = \App\Models\Contact::query()->where('user_linked_id', $user->id)->exists();
            abort_unless($user->isPromotor() || $hasLinkedContact, 403);

            return $next($request);
        });
    }

    public function index(): View
    {
        $user = auth()->user();

        $entries = PointLedgerEntry::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $balance = $user->pointsBalance();

        return view('member.poin.index', compact('entries', 'balance'));
    }
}
