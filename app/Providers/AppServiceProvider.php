<?php

namespace App\Providers;

use App\Enums\LicenseStatus;
use App\Enums\LicenseTier;
use App\Integrations\Telegram\AdminNotifierContract;
use App\Integrations\Telegram\TelegramAdminNotificationAdapter;
use App\Jobs\ProviderHealthCheckAllJob;
use App\Models\ApplicationLicense;
use App\Models\Campaign;
use App\Models\CommissionEntry;
use App\Models\Contact;
use App\Models\Course;
use App\Models\DigitalAsset;
use App\Models\Fulfillment;
use App\Models\IntegrationConnection;
use App\Models\MessageTemplate;
use App\Models\Payout;
use App\Models\Pipeline;
use App\Models\PointLedgerEntry;
use App\Models\ProductLicenseKey;
use App\Models\ReferralLink;
use App\Models\StifinResult;
use App\Models\User;
use App\Policies\CampaignPolicy;
use App\Policies\CommissionPolicy;
use App\Policies\ContactPolicy;
use App\Policies\CoursePolicy;
use App\Policies\DigitalAssetPolicy;
use App\Policies\IntegrationConnectionPolicy;
use App\Policies\LicenseKeyPolicy;
use App\Policies\MessageTemplatePolicy;
use App\Policies\PayoutPolicy;
use App\Policies\PipelinePolicy;
use App\Policies\PointLedgerPolicy;
use App\Policies\ReferralLinkPolicy;
use App\Policies\StifinResultPolicy;
use App\Services\Analytics\DailySnapshotService;
use App\Services\Licensing\ApplicationLicenseValidatorService;
use App\Services\Tracking\PixelInjectionService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class AppServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Contact::class => ContactPolicy::class,
        Pipeline::class => PipelinePolicy::class,
        CommissionEntry::class => CommissionPolicy::class,
        Payout::class => PayoutPolicy::class,
        Course::class => CoursePolicy::class,
        StifinResult::class => StifinResultPolicy::class,
        DigitalAsset::class => DigitalAssetPolicy::class,
        ProductLicenseKey::class => LicenseKeyPolicy::class,
        Campaign::class => CampaignPolicy::class,
        MessageTemplate::class => MessageTemplatePolicy::class,
        IntegrationConnection::class => IntegrationConnectionPolicy::class,
        PointLedgerEntry::class => PointLedgerPolicy::class,
        ReferralLink::class => ReferralLinkPolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(AdminNotifierContract::class, function ($app) {
            try {
                $telegramConfigured = false;
                if (Schema::hasTable('integration_connections')) {
                    $count = IntegrationConnection::query()
                        ->whereRaw('LOWER(provider_category) = ?', ['telegram'])
                        ->orWhere('provider_type', 'telegram_bot')
                        ->count();
                    if ($count > 0) {
                        $telegramConfigured = true;
                    }
                }
                if (! $telegramConfigured) {
                    $botToken = config('services.telegram.bot_token');
                    $chatId = config('services.telegram.admin_chat_id');
                    if (! empty($botToken) && ! empty($chatId)) {
                        $telegramConfigured = true;
                    }
                }
                if ($telegramConfigured) {
                    return $app->make(TelegramAdminNotificationAdapter::class);
                }
            } catch (\Throwable $e) {
            }

            return new class implements AdminNotifierContract
            {
                public function sendAlert(string $title, string $message, array $context = []): void
                {
                    Log::warning('[AdminNotifier:Fallback] Alert: '.$title.' | '.$message, $context);
                }

                public function sendPayoutRequest(Payout $payout): void
                {
                    Log::warning('[AdminNotifier:Fallback] PayoutRequest id='.$payout->id);
                }

                public function sendNeedsReviewVoucher(Fulfillment $fulfillment): void
                {
                    Log::warning('[AdminNotifier:Fallback] NeedsReviewVoucher fulfillment_id='.$fulfillment->id);
                }

                public function sendProviderDegraded(IntegrationConnection $connection, string $reason): void
                {
                    Log::warning('[AdminNotifier:Fallback] ProviderDegraded conn='.$connection->id.' '.$reason);
                }
            };
        });
    }

    public function boot(
        ApplicationLicenseValidatorService $licenseValidator,
        PixelInjectionService $pixelService,
    ): void {
        $this->registerPolicies();

        try {
            if (app()->environment(['local', 'testing']) && Schema::hasTable('application_licenses')) {
                $this->seedLocalStarterLicense($licenseValidator);
            }
        } catch (\Throwable $e) {
        }

        try {
            if (app()->bound(Schedule::class) || method_exists($this->app, 'make')) {
                $this->app->booted(function () {
                    try {
                        $schedule = $this->app->make(Schedule::class);
                        $schedule->call(function () {
                            try {
                                $svc = app(DailySnapshotService::class);
                                $svc->generateForDate(today()->subDay());
                            } catch (\Throwable $e) {
                            }
                        })
                            ->dailyAt('00:05')
                            ->name('stiflow-daily-analytics-snapshot')
                            ->withoutOverlapping(30)
                            ->onOneServer();

                        $schedule->job(ProviderHealthCheckAllJob::class)
                            ->everyFifteenMinutes()
                            ->name('stiflow-provider-health-check-15m')
                            ->withoutOverlapping(20)
                            ->onOneServer();
                    } catch (\Throwable $e) {
                    }
                });
            }
        } catch (\Throwable $e) {
        }

        try {
            View::composer(['welcome', 'public.catalog.*', 'public.landing.*'], function ($view) use ($pixelService) {
                try {
                    $view->with([
                        'pixelHeadScripts' => $pixelService->renderHeadScripts(),
                        'pixelBodyScripts' => $pixelService->renderBodyScripts(),
                    ]);
                } catch (\Throwable $e) {
                    $view->with(['pixelHeadScripts' => '', 'pixelBodyScripts' => '']);
                }
            });
        } catch (\Throwable $e) {
        }

        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->isAdmin()) {
                return true;
            }

            if ($user->isStaff()) {
                $permKeys = array_slice(func_get_args(), 2);
                if (! empty($permKeys)) {
                    foreach ($permKeys as $pk) {
                        if (is_array($pk)) {
                            if ($user->hasPermission($pk)) {
                                return true;
                            }
                        } elseif (is_string($pk)) {
                            if ($user->hasPermission($pk)) {
                                return true;
                            }
                        }
                    }
                }
            }

            return null;
        });
    }

    private function seedLocalStarterLicense(ApplicationLicenseValidatorService $licenseValidator): void
    {
        $uuid = $licenseValidator->getOrGenerateInstallationUuid();

        ApplicationLicense::firstOrCreate(
            ['installation_uuid' => $uuid],
            [
                'license_key_sha256' => hash('sha256', 'LOCAL-DEV-STARTER-LICENSE-'.($uuid ?? Str::random(16))),
                'domain_name' => parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost',
                'customer_name' => 'Local Development',
                'customer_email' => 'dev@stiflow.local',
                'tier' => LicenseTier::Starter,
                'seats_allowed' => 50,
                'max_branches' => 5,
                'activated_at' => now()->subYear(),
                'expires_at' => now()->setYear(2099)->endOfYear(),
                'last_validated_at' => now(),
                'validation_signed_lease_json' => [
                    'env' => app()->environment(),
                    'seeded_by' => 'AppServiceProvider',
                    'mode' => 'local-development',
                ],
                'lease_valid_until' => now()->setYear(2099)->endOfYear(),
                'status' => LicenseStatus::Active,
            ]
        );
    }
}
