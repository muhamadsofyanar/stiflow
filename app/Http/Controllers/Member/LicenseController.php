<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ProductLicenseKey;
use Illuminate\Contracts\View\View;

class LicenseController extends Controller
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

        $licenses = ProductLicenseKey::query()
            ->where('user_id', $user->id)
            ->orWhere('activated_by_user_id', $user->id)
            ->with(['product', 'activations'])
            ->latest()
            ->paginate(20);

        return view('member.lisensi.index', compact('licenses'));
    }
}
