<?php

use App\Http\Controllers\Api\LicenseValidationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/license/validate', [LicenseValidationController::class, 'validate'])->name('api.license.validate');
});

Route::middleware(['throttle:120,1'])->prefix('webhook')->name('api.webhook.')->group(function () {
    Route::post('/onesender', \App\Http\Controllers\Api\Webhook\OneSenderWebhookController::class)->name('onesender');
    Route::post('/starsender', \App\Http\Controllers\Api\Webhook\StarSenderWebhookController::class)->name('starsender');
    Route::post('/email/{provider}', \App\Http\Controllers\Api\Webhook\EmailProviderWebhookController::class)->name('email');
    Route::post('/payments/{provider}', \App\Http\Controllers\Api\Webhook\PaymentGatewayWebhookController::class)->name('payments');
});
