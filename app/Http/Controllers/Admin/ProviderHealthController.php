<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IntegrationConnectionStatus;
use App\Http\Controllers\Controller;
use App\Models\IntegrationConnection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProviderHealthController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:integrations.manage');
    }

    public function index(Request $request): View
    {
        $query = IntegrationConnection::query()
            ->with('ownedBy')
            ->orderBy('is_primary', 'desc')
            ->orderBy('provider_category')
            ->orderBy('display_name');

        $statusFilter = $request->input('status');
        $categoryFilter = $request->input('category');

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($categoryFilter) {
            $query->where('provider_category', $categoryFilter);
        }

        $connections = $query->get();

        $healthSummary = [
            'total' => $connections->count(),
            'healthy' => $connections->where('status', IntegrationConnectionStatus::Healthy->value)->count(),
            'degraded' => $connections->where('status', IntegrationConnectionStatus::Degraded->value)->count(),
            'offline' => $connections->where('status', IntegrationConnectionStatus::Offline->value)->count(),
            'error' => $connections->where('status', IntegrationConnectionStatus::Error->value)->count(),
            'disabled' => $connections->where('status', IntegrationConnectionStatus::Disabled->value)->count(),
            'untested' => $connections->where('status', IntegrationConnectionStatus::Configured->value)->count(),
        ];

        $lastCheckAt = Cache::get('health_last_run_at');

        return view('admin.integrations.health', compact(
            'connections',
            'healthSummary',
            'lastCheckAt',
            'statusFilter',
            'categoryFilter'
        ));
    }

    public function runCheck(Request $request): RedirectResponse
    {
        try {
            \App\Jobs\ProviderHealthCheckAllJob::dispatch();

            return redirect()->back()->with('success', 'Health check sedang berjalan di background. Cek beberapa saat lagi.');
        } catch (\Throwable $e) {
            Log::error('Provider health dispatch error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal menjalankan health check: ' . $e->getMessage());
        }
    }

    public function runSingle(Request $request, IntegrationConnection $integration): RedirectResponse
    {
        try {
            $healthChecker = app(\App\Integrations\Health\AggregateConnectionHealthChecker::class);
            $result = $healthChecker->checkSingle($integration);

            if ($result['success'] ?? false) {
                return redirect()->back()->with('success', "Connection {$integration->display_name}: status OK.");
            }

            return redirect()->back()->with('error', "Connection gagal: " . ($result['message'] ?? 'Unknown error'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Check gagal: ' . $e->getMessage());
        }
    }
}
