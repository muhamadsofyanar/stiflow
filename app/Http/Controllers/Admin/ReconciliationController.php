<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Enums\ReconciliationResolution;
use App\Enums\ReconciliationStatus;
use App\Http\Controllers\Controller;
use App\Models\ReconciliationCase;
use App\Services\Audit\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReconciliationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status') ?? ReconciliationStatus::Open->value;
        $cases = ReconciliationCase::query()
            ->with(['subject', 'assignee'])
            ->where('status', $status)
            ->latest()
            ->paginate(20);

        return view('admin.reconciliation.index', compact('cases', 'status'));
    }

    public function resolve(Request $request, ReconciliationCase $case): RedirectResponse
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $resolution = $request->input('resolution', ReconciliationResolution::ManualSuccess->value);
        $note = trim((string) $request->input('note', ''));
        if (! in_array($resolution, [ReconciliationResolution::ManualSuccess->value, ReconciliationResolution::ManualRollback->value, ReconciliationResolution::NoAction->value], true)) {
            abort(400, 'Resolusi tidak valid.');
        }

        $before = $case->toArray();
        $case->status = ReconciliationStatus::Resolved;
        $case->resolution = $resolution;
        $case->resolution_note = $note;
        $case->assigned_to = auth()->id();
        $case->resolved_at = now();
        $case->save();

        if ($case->subject_type === \App\Models\Fulfillment::class) {
            $fulfillment = $case->subject;
            if ($fulfillment) {
                if ($resolution === ReconciliationResolution::ManualSuccess->value) {
                    $fulfillment->status = \App\Enums\FulfillmentStatus::Success;
                    $fulfillment->completed_at = now();
                    $fulfillment->save();
                    if ($fulfillment->orderItem && $fulfillment->orderItem->order) {
                        $order = $fulfillment->orderItem->order;
                        $order->status = \App\Enums\OrderStatus::Completed;
                        $order->completed_at = now();
                        $order->save();
                    }
                } elseif ($resolution === ReconciliationResolution::ManualRollback->value) {
                    $fulfillment->status = \App\Enums\FulfillmentStatus::Failed;
                    $fulfillment->completed_at = now();
                    $fulfillment->save();
                }
            }
        }

        AuditService::record(
            action: AuditAction::ReconciliationResolved,
            subject: $case,
            before: $before,
            after: $case->toArray(),
        );

        return back()->with('status', 'Kasus rekonsiliasi diselesaikan.');
    }
}
