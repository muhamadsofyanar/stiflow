<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoterProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PromotorController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $profiles = PromoterProfile::query()
            ->with('user')
            ->when($status, fn ($q) => $q->where('verification_status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where('stifin_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15);

        return view('admin.promotors.index', compact('profiles', 'status', 'search'));
    }
}
