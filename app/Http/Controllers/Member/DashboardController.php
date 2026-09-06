<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Enrollment;
use App\Models\StifinResult;
use App\Models\DownloadGrant;
use App\Models\ProductLicenseKey;
use App\Models\PointLedgerEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $hasLinkedContact = \App\Models\Contact::query()->where('user_linked_id', $user->id)->exists();
            abort_unless($user->isPromotor() || $hasLinkedContact, 403, 'Anda tidak memiliki akses member area.');

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $userId = $user->id;
        $contactQuery = \App\Models\Contact::query()->where('user_linked_id', $userId);
        $contactId = (clone $contactQuery)->value('id');

        $cardData = [
            'orders_count' => Order::query()->where('user_id', $userId)->count(),
            'kelas_count' => Enrollment::query()->where('user_id', $userId)->count(),
            'stifin_count' => StifinResult::query()
                ->where(function ($q) use ($userId, $contactId) {
                    $q->whereHas('order', fn ($o) => $o->where('user_id', $userId))
                        ->orWhere('contact_id', $contactId);
                })->count(),
            'unduhan_count' => DownloadGrant::query()->where('user_id', $userId)->count(),
            'lisensi_count' => ProductLicenseKey::query()
                ->where('user_id', $userId)
                ->orWhere('activated_by_user_id', $userId)
                ->count(),
            'poin_balance' => $user->pointsBalance(),
            'promo_count' => \App\Models\Coupon::query()->where('assigned_to_user_id', $userId)->count(),
            'profil' => $user,
        ];

        return view('member.dashboard', compact('cardData'));
    }

    public function unduhan(): View
    {
        $user = auth()->user();
        $grants = DownloadGrant::query()
            ->where('user_id', $user->id)
            ->with(['asset'])
            ->latest()
            ->paginate(20);

        return view('member.unduhan.index', compact('grants'));
    }

    public function profil(): View
    {
        $user = auth()->user();
        $contact = \App\Models\Contact::query()->where('user_linked_id', $user->id)->first();

        return view('member.profil.index', compact('user', 'contact'));
    }
}
