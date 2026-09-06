<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\Fulfillment\DownloadGrantService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureDownloadController extends Controller
{
    public function __invoke(Request $request, string $grantToken, DownloadGrantService $service): StreamedResponse
    {
        return $service->redeemForToken($grantToken, [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
