<?php

use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\LeadCaptureController;
use App\Http\Controllers\Public\ReferralLinkRedirectController;
use Illuminate\Support\Facades\Route;

Route::model('referralLink', \App\Models\ReferralLink::class);
Route::model('contact', \App\Models\Contact::class);
Route::model('course', \App\Models\Course::class);
Route::model('pipeline', \App\Models\Pipeline::class);
Route::model('campaign', \App\Models\Campaign::class);
Route::model('payout', \App\Models\Payout::class);
Route::model('product', \App\Models\Product::class);
Route::model('variant', \App\Models\ProductVariant::class);
Route::model('stage', \App\Models\PipelineStage::class);
Route::model('template', \App\Models\MessageTemplate::class);
Route::model('list', \App\Models\ContactList::class);
Route::model('segment', \App\Models\Segment::class);
Route::model('integration', \App\Models\IntegrationConnection::class);
Route::model('stifinResult', \App\Models\StifinResult::class);

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome', [
        'branch' => \App\Models\BranchSetting::query()->first(),
        'brand' => \App\Models\BrandSetting::query()->first(),
        'voucherProduct' => \App\Models\Product::query()->voucher()->active()->first(),
    ]);
});

Route::get('/r/{referralLink:slug}', [ReferralLinkRedirectController::class, 'redirect'])->name('referral.redirect');
Route::get('/lead-capture', [LeadCaptureController::class, 'showForm'])->name('lead-capture.form');
Route::post('/lead-capture', [LeadCaptureController::class, 'store'])->name('lead-capture.store');
Route::post('/license/validate', [\App\Http\Controllers\Api\LicenseValidationController::class, 'validateWeb'])->name('license.validate.web');

Route::get('/dashboard', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }
    if ($user->isStaffOrAbove()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->isPromotor()) {
        return redirect()->route('promotor.dashboard');
    }

    return redirect()->route('member.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/payment-proofs/{proof}/download', [PaymentProofController::class, 'download'])
        ->name('payment-proofs.download')
        ->can('download', 'proof');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', \App\Http\Controllers\Admin\DashboardController::class)->name('dashboard');
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('/payment-proofs/{proof}/approve', [\App\Http\Controllers\Admin\OrderController::class, 'approveProof'])->name('payment-proofs.approve');
    Route::post('/payment-proofs/{proof}/reject', [\App\Http\Controllers\Admin\OrderController::class, 'rejectProof'])->name('payment-proofs.reject');

    Route::get('/promotors', [\App\Http\Controllers\Admin\PromotorController::class, 'index'])->name('promotors.index');
    Route::post('/promotors/{profile}/verify', [\App\Http\Controllers\Admin\PromotorVerifyController::class, 'verify'])->name('promoters.verify')->middleware('can:promoters.verify');

    Route::get('/products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::post('/products/{product}/toggle-active', [\App\Http\Controllers\Admin\ProductController::class, 'toggleActive'])->name('products.toggle-active');

    Route::get('/settings/branch', [\App\Http\Controllers\Admin\BranchSettingController::class, 'edit'])->name('branch-settings.edit');
    Route::put('/settings/branch', [\App\Http\Controllers\Admin\BranchSettingController::class, 'update'])->name('branch-settings.update');

    Route::get('/reconciliation', [\App\Http\Controllers\Admin\ReconciliationController::class, 'index'])->name('reconciliation.index');
    Route::post('/reconciliation/{case}/resolve', [\App\Http\Controllers\Admin\ReconciliationController::class, 'resolve'])->name('reconciliation.resolve');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('contacts', \App\Http\Controllers\Admin\ContactController::class);
    Route::resource('pipelines', \App\Http\Controllers\Admin\PipelineController::class);
    Route::resource('pipelines.stages', \App\Http\Controllers\Admin\PipelineController::class)->only(['store', 'update', 'destroy']);
    Route::resource('products-catalog', \App\Http\Controllers\Admin\ProductCatalogController::class);
    Route::resource('variants', \App\Http\Controllers\Admin\VariantController::class);
    Route::resource('courses', \App\Http\Controllers\Admin\CourseManagementController::class);
    Route::resource('stifin-results', \App\Http\Controllers\Admin\StifinResultAdminController::class);
    Route::resource('campaigns', \App\Http\Controllers\Admin\CampaignAdminController::class);
    Route::resource('templates', \App\Http\Controllers\Admin\MessageTemplateAdminController::class);
    Route::resource('lists', \App\Http\Controllers\Admin\CampaignAdminController::class);
    Route::resource('segments', \App\Http\Controllers\Admin\CampaignAdminController::class);
    Route::get('integrations', [\App\Http\Controllers\Admin\IntegrationConnectionController::class, 'index'])->name('integrations.index')->middleware('can:integrations.manage');
    Route::get('integrations/{integration}/edit', [\App\Http\Controllers\Admin\IntegrationConnectionController::class, 'edit'])->name('integrations.edit')->middleware('can:integrations.manage');
    Route::put('integrations/{integration}', [\App\Http\Controllers\Admin\IntegrationConnectionController::class, 'update'])->name('integrations.update')->middleware('can:integrations.manage');
    Route::post('integrations', [\App\Http\Controllers\Admin\IntegrationConnectionController::class, 'store'])->name('integrations.store')->middleware('can:integrations.manage');
    Route::delete('integrations/{integration}', [\App\Http\Controllers\Admin\IntegrationConnectionController::class, 'destroy'])->name('integrations.destroy')->middleware('can:integrations.manage');
    Route::get('payouts', [\App\Http\Controllers\Admin\PayoutAdminController::class, 'index'])->name('payouts.index');
    Route::get('payouts/{payout}', [\App\Http\Controllers\Admin\PayoutAdminController::class, 'show'])->name('payouts.show');
    Route::post('payouts/{payout}/approve', [\App\Http\Controllers\Admin\PayoutAdminController::class, 'approve'])->name('payouts.approve')->middleware('can:payouts.approve');
    Route::post('payouts/{payout}/lock', [\App\Http\Controllers\Admin\PayoutAdminController::class, 'lock'])->name('payouts.lock')->middleware('can:payouts.approve');
    Route::get('staff/permissions', [\App\Http\Controllers\Admin\StaffPermissionController::class, 'index'])->name('staff.permissions')->middleware('can:users.manage');
    Route::post('staff/permissions', [\App\Http\Controllers\Admin\StaffPermissionController::class, 'save'])->name('staff.permissions.save')->middleware('can:users.manage');
    Route::get('audit', [\App\Http\Controllers\Admin\AuditLogViewerController::class, 'index'])->name('audit.index')->middleware('can:audit.view');
    Route::get('points-ledger', [\App\Http\Controllers\Admin\PointLedgerAdminController::class, 'index'])->name('points-ledger.index');
    Route::post('points-ledger', [\App\Http\Controllers\Admin\PointLedgerAdminController::class, 'store'])->name('points-ledger.store');
});

Route::middleware(['auth', 'verified', 'verified_promotor'])->prefix('promotor')->name('promotor.')->group(function () {
    Route::get('/', \App\Http\Controllers\Promotor\DashboardController::class)->name('dashboard');
    Route::get('/checkout', [\App\Http\Controllers\Promotor\CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/place-order', [\App\Http\Controllers\Promotor\CheckoutController::class, 'placeOrder'])->name('checkout.place-order');

    Route::get('/orders', [\App\Http\Controllers\Promotor\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Promotor\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/upload-proof', [\App\Http\Controllers\Promotor\OrderController::class, 'uploadProof'])->name('orders.upload-proof');
});

Route::middleware(['auth', 'verified', 'verified_promotor'])->prefix('promotor')->name('promotor.')->group(function () {
    Route::get('/crm', [\App\Http\Controllers\Promotor\CrmController::class, 'index'])->name('crm.index');
    Route::get('/crm/contacts/{contact}', [\App\Http\Controllers\Promotor\CrmController::class, 'show'])->name('crm.contacts.show');
    Route::get('/crm/boards/{pipeline?}', [\App\Http\Controllers\Promotor\CrmController::class, 'board'])->name('crm.boards');
    Route::get('/affiliate/tree', [\App\Http\Controllers\Promotor\AffiliateController::class, 'tree'])->name('affiliate.tree');
    Route::get('/komisi', [\App\Http\Controllers\Promotor\CommissionController::class, 'index'])->name('komisi.index');
    Route::get('/referral-links', [\App\Http\Controllers\Promotor\ReferralLinkController::class, 'index'])->name('referral-links.index');
    Route::post('/referral-links', [\App\Http\Controllers\Promotor\ReferralLinkController::class, 'store'])->name('referral-links.store');
    Route::get('/points', [\App\Http\Controllers\Promotor\MemberAreaController::class, 'points'])->name('points.index');
    Route::get('/kelas-saya', [\App\Http\Controllers\Promotor\MemberAreaController::class, 'kelas'])->name('kelas-saya.index');
    Route::get('/hasil-stifin', [\App\Http\Controllers\Promotor\MemberAreaController::class, 'hasilStifin'])->name('hasil-stifin.index');
    Route::get('/unduhan', [\App\Http\Controllers\Promotor\MemberAreaController::class, 'unduhan'])->name('unduhan.index');
});

Route::middleware(['auth', 'verified'])->prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Member\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [\App\Http\Controllers\Member\OrderController::class, 'index'])->name('orders.index');
    Route::get('/kelas', [\App\Http\Controllers\Member\CourseController::class, 'index'])->name('kelas.index');
    Route::get('/hasil-stifin', [\App\Http\Controllers\Member\ResultController::class, 'index'])->name('hasil-stifin.index');
    Route::get('/unduhan', [\App\Http\Controllers\Member\DashboardController::class, 'unduhan'])->name('unduhan.index');
    Route::get('/lisensi', [\App\Http\Controllers\Member\LicenseController::class, 'index'])->name('lisensi.index');
    Route::get('/poin', [\App\Http\Controllers\Member\PointController::class, 'index'])->name('poin.index');
    Route::get('/profil', [\App\Http\Controllers\Member\DashboardController::class, 'profil'])->name('profil.index');
});

Route::model('landingPage', \App\Models\LandingPage::class);

Route::middleware(['guest', 'throttle:60,1'])->prefix('')->name('public.')->group(function () {
    Route::get('/katalog', [\App\Http\Controllers\Public\CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/produk/{product:slug}', [\App\Http\Controllers\Public\CatalogController::class, 'show'])->name('catalog.show');
    Route::get('/lp/{landingPage:slug}', [\App\Http\Controllers\Public\LeadLandingPageController::class, 'show'])->name('landing.show');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('catalog')->name('catalog.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CatalogManagementController::class, 'index'])->name('index');
        Route::get('/{product}/edit', [\App\Http\Controllers\Admin\CatalogManagementController::class, 'edit'])->name('edit');
        Route::put('/{product}', [\App\Http\Controllers\Admin\CatalogManagementController::class, 'update'])->name('update');
        Route::post('/{product}/publish', [\App\Http\Controllers\Admin\CatalogManagementController::class, 'publish'])->name('publish');
        Route::post('/{product}/unpublish', [\App\Http\Controllers\Admin\CatalogManagementController::class, 'unpublish'])->name('unpublish');
        Route::post('/reorder', [\App\Http\Controllers\Admin\CatalogManagementController::class, 'reorder'])->name('reorder');
    });

    Route::prefix('landing-pages')->name('landing-pages.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\LandingPageBuilderController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\LandingPageBuilderController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\LandingPageBuilderController::class, 'store'])->name('store');
        Route::get('/{landingPage}/edit', [\App\Http\Controllers\Admin\LandingPageBuilderController::class, 'edit'])->name('edit');
        Route::put('/{landingPage}', [\App\Http\Controllers\Admin\LandingPageBuilderController::class, 'update'])->name('update');
    });

    Route::get('/analytics', [\App\Http\Controllers\Admin\AnalyticsDashboardController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/dashboard', [\App\Http\Controllers\Admin\AnalyticsDashboardController::class, 'index'])->name('analytics.dashboard');

    Route::prefix('license')->name('license.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ApplicationLicenseController::class, 'show'])->name('show');
        Route::post('/activate', [\App\Http\Controllers\Admin\ApplicationLicenseController::class, 'activate'])->name('activate');
    });

    Route::prefix('updates')->name('updates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UpdateChannelController::class, 'index'])->name('index');
        Route::post('/check-now', [\App\Http\Controllers\Admin\UpdateChannelController::class, 'checkNow'])->name('check-now');
    });

    Route::prefix('payment-gateways')->name('payment-gateways.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PaymentGatewayConfigController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\PaymentGatewayConfigController::class, 'store'])->name('store');
        Route::post('/{gateway}/toggle', [\App\Http\Controllers\Admin\PaymentGatewayConfigController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('social-proof')->name('social-proof.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SocialProofAdminController::class, 'index'])->name('index');
        Route::post('/{product}', [\App\Http\Controllers\Admin\SocialProofAdminController::class, 'update'])->name('update');
    });
});

Route::middleware(['auth', 'verified'])->prefix('member')->name('member.')->group(function () {
    Route::get('/downloads/{grantToken}', \App\Http\Controllers\Member\SecureDownloadController::class)->name('downloads.stream');
});

Route::middleware(['guest', 'throttle:60,1'])->prefix('')->name('public.')->group(function () {
    Route::post('/coupon/apply', [\App\Http\Controllers\Public\CouponRedeemController::class, 'apply'])->name('coupon.apply');
    Route::post('/coupon/remove', [\App\Http\Controllers\Public\CouponRedeemController::class, 'remove'])->name('coupon.remove');
    Route::get('/coupon/result', [\App\Http\Controllers\Public\CouponRedeemController::class, 'showResult'])->name('coupon.result');
    Route::get('/checkout/{product:slug}', [\App\Http\Controllers\Public\CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/store', [\App\Http\Controllers\Public\CheckoutController::class, 'store'])->name('checkout.store');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponManagementController::class)->middleware('can:coupon.manage');
    Route::get('/commissions/manual-process', [\App\Http\Controllers\Admin\CommissionProcessManualController::class, 'index'])->name('commissions.manual-process');
    Route::post('/commissions/process-order/{order}', [\App\Http\Controllers\Admin\CommissionProcessManualController::class, 'processOrder'])->name('commissions.process-order');
    Route::get('/payouts/{payout}/upload-proof', [\App\Http\Controllers\Admin\PayoutProofController::class, 'showUpload'])->name('payouts.show-upload-proof')->middleware('can:payouts.approve');
    Route::post('/payouts/{payout}/upload-proof', [\App\Http\Controllers\Admin\PayoutProofController::class, 'uploadProof'])->name('payouts.upload-proof')->middleware('can:payouts.approve');
    Route::get('/payouts/export', [\App\Http\Controllers\Admin\PayoutProofController::class, 'downloadExport'])->name('payouts.download-export')->middleware('can:payouts.approve');

    Route::get('/integrations/health', [\App\Http\Controllers\Admin\ProviderHealthController::class, 'index'])->name('integrations.health')->middleware('can:integrations.manage');
    Route::post('/integrations/health/run', [\App\Http\Controllers\Admin\ProviderHealthController::class, 'runCheck'])->name('integrations.health.run')->middleware('can:integrations.manage');
    Route::post('/integrations/health/{integration}', [\App\Http\Controllers\Admin\ProviderHealthController::class, 'runSingle'])->name('integrations.health.single')->middleware('can:integrations.manage');
    Route::get('/automations/follow-ups', [\App\Http\Controllers\Admin\FollowUpAutomationController::class, 'index'])->name('automations.follow-ups');
    Route::post('/automations/follow-ups/{stage}/toggle', [\App\Http\Controllers\Admin\FollowUpAutomationController::class, 'toggleStage'])->name('automations.follow-ups.toggle');
    Route::post('/automations/follow-ups/test-run', [\App\Http\Controllers\Admin\FollowUpAutomationController::class, 'testRun'])->name('automations.follow-ups.test-run');
    Route::get('/webhook-logs', [\App\Http\Controllers\Admin\ProviderHealthController::class, 'webhookLogs'])->name('webhook.logs');
});

Route::middleware(['auth', 'verified', 'verified_promotor'])->prefix('promotor')->name('promotor.')->group(function () {
    Route::get('/komisi/pending', [\App\Http\Controllers\Promotor\CommissionDashboardController::class, 'pending'])->name('komisi.pending');
    Route::get('/komisi/payable', [\App\Http\Controllers\Promotor\CommissionDashboardController::class, 'payable'])->name('komisi.payable');
    Route::get('/komisi/paid', [\App\Http\Controllers\Promotor\CommissionDashboardController::class, 'paid'])->name('komisi.paid');
    Route::get('/komisi/reversed', [\App\Http\Controllers\Promotor\CommissionDashboardController::class, 'reversed'])->name('komisi.reversed');
});

require __DIR__.'/auth.php';
