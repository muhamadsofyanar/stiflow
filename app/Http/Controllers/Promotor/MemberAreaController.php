<?php

namespace App\Http\Controllers\Promotor;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\PointLedgerEntry;
use App\Models\StifinResult;
use App\Models\DownloadGrant;
use Illuminate\Contracts\View\View;

class MemberAreaController extends Controller
{
    public function points(): View
    {
        $user = auth()->user();

        $entries = PointLedgerEntry::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        $balance = $user->pointsBalance();

        return view('promotor.points.index', compact('entries', 'balance'));
    }

    public function kelas(): View
    {
        $user = auth()->user();

        $enrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->with(['course.modules.lessons', 'progress'])
            ->latest()
            ->paginate(15);

        return view('promotor.kelas-saya.index', compact('enrollments'));
    }

    public function hasilStifin(): View
    {
        $user = auth()->user();

        $results = StifinResult::query()
            ->whereHas('contact', fn ($q) => $q->where('user_linked_id', $user->id))
            ->orWhereHas('order', fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->paginate(15);

        return view('promotor.hasil-stifin.index', compact('results'));
    }

    public function unduhan(): View
    {
        $user = auth()->user();

        $grants = DownloadGrant::query()
            ->where('user_id', $user->id)
            ->with(['asset'])
            ->latest()
            ->paginate(20);

        return view('promotor.unduhan.index', compact('grants'));
    }
}
