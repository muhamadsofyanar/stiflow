<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PointDirection;
use App\Enums\PointEntryType;
use App\Http\Controllers\Controller;
use App\Models\PointLedgerEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PointLedgerAdminController extends Controller
{
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
            'entry_type' => 'required|string',
            'reference_type' => 'nullable|string|max:100',
            'reference_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        PointLedgerEntry::query()->create([
            ...$validated,
            'direction' => $validated['direction'] === 'credit' ? PointDirection::Credit : PointDirection::Debit,
            'entry_type' => $validated['entry_type'] ?? PointEntryType::ManualAdjustment,
            'created_by_user_id' => auth()->id(),
        ]);

        return back()->with('status', 'Entry poin ditambahkan.');
    }
}
