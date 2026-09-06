<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\StifinResult;
use Illuminate\Contracts\View\View;

class ResultController extends Controller
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
        $contactId = \App\Models\Contact::query()->where('user_linked_id', $user->id)->value('id');

        $results = StifinResult::query()
            ->where(function ($q) use ($user, $contactId) {
                $q->whereHas('order', fn ($o) => $o->where('user_id', $user->id))
                    ->orWhere('contact_id', $contactId);
            })
            ->with(['contact', 'order'])
            ->latest()
            ->paginate(15);

        return view('member.hasil-stifin.index', compact('results'));
    }
}
