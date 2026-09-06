<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UpdateChannelRelease;
use App\Services\Update\UpdateChannelCheckerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateChannelController extends Controller
{
    public function index(Request $request, UpdateChannelCheckerService $checker): View
    {
        $channel = $request->input('channel', 'Stable');

        $releases = UpdateChannelRelease::query()
            ->when($channel !== 'All', fn ($q) => $q->where('channel', $channel))
            ->orderBy('published_at', 'desc')
            ->paginate(20);

        $latestRelease = $checker->getLatestRelease($channel === 'All' ? 'Stable' : $channel);
        $currentVersion = config('app.version', '1.0.0');

        return view('admin.updates.index', compact('releases', 'latestRelease', 'currentVersion', 'channel'));
    }

    public function checkNow(UpdateChannelCheckerService $checker): RedirectResponse
    {
        $checker->checkForNewRelease();

        return redirect()->route('admin.updates.index')->with('status', 'Pengecekan update selesai.');
    }
}
