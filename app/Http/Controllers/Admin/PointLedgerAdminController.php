<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointLedgerEntry;
use App\Services\Points\PointLedgerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class PointLedgerAdminController extends Controller
{
    public function __construct(private readonly PointLedgerService $pointLedgerService)
    {
    }

    public function index(Request $request): View
    {
        $query = PointLedgerEntry::query()->with(['user'])->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $entries = $query->paginate(30);

        return view('admin.points-ledger.index', compact('entries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'direction' => 'required|in:credit,debit',
            'amount_points' => 'required|integer|min:1',
            'notes' => 'required|string|max:500',
        ]);

        try {
            $this->pointLedgerService->adjust(
                userId: (int) $validated['user_id'],
                direction: $validated['direction'],
                amountPoints: (int) $validated['amount_points'],
                reasonText: $validated['notes'],
                actor: $request->user(),
            );
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['amount_points' => $exception->getMessage()]);
        }

        return back()->with('status', 'Entry poin ditambahkan.');
    }
}
