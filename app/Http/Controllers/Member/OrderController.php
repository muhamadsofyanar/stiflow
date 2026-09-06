<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

class OrderController extends Controller
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

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with(['items', 'paymentAttempts'])
            ->latest()
            ->paginate(20);

        return view('member.orders.index', compact('orders'));
    }
}
