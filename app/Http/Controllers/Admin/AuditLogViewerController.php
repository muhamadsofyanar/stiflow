<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogViewerController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with(['actor'])->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('actor_user_id', $request->input('user_id'));
        }

        $logs = $query->paginate(50);

        return view('admin.audit.index', compact('logs'));
    }
}
