<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\LandingPageVisit;
use App\Services\Landing\SimpleBlockRendererService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeadLandingPageController extends Controller
{
    public function show(Request $request, LandingPage $landingPage, SimpleBlockRendererService $renderer): View
    {
        abort_unless($landingPage->isPublished(), 404);

        LandingPageVisit::query()->create([
            'landing_page_id' => $landingPage->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
            'visited_at' => now(),
            'cookie_uuid' => $request->cookie('lp_uuid') ?? (string) Str::uuid(),
        ]);

        $blocks = $landingPage->blocks_json ?? [];
        if (is_string($blocks)) {
            try {
                $blocks = json_decode($blocks, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $blocks = [];
            }
        }
        $blocksHtml = $renderer->render(is_array($blocks) ? $blocks : []);
        $meta = $landingPage->meta_json ?? [];

        return view('public.landing.show', compact('landingPage', 'blocksHtml', 'meta'));
    }
}
