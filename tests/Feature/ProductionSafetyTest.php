<?php

namespace Tests\Feature;

use App\Exceptions\ProviderNotConfigured;
use App\Http\Controllers\Api\Webhook\PaymentGatewayWebhookController;
use App\Integrations\Payments\FinpayPaymentAdapter;
use App\Integrations\Payments\MidtransPaymentAdapter;
use App\Integrations\Payments\XenditPaymentAdapter;
use App\Models\Product;
use App\Services\Licensing\ApplicationLicenseValidatorService;
use App\Services\Payments\PostPaymentHookCoordinator;
use App\Services\SocialProof\RecentPurchaseService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use LogicException;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductionSafetyTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('unreleasedGateways')]
    public function test_unreleased_gateway_cannot_create_a_simulated_charge(string $adapter): void
    {
        $this->expectException(ProviderNotConfigured::class);

        app($adapter)->createCharge(['order_id' => 'TEST-1', 'amount' => 100000]);
    }

    public static function unreleasedGateways(): array
    {
        return [
            [MidtransPaymentAdapter::class],
            [XenditPaymentAdapter::class],
            [FinpayPaymentAdapter::class],
        ];
    }

    public function test_empty_purchase_history_never_generates_fake_social_proof(): void
    {
        $product = Product::query()->create([
            'type' => 'digital',
            'name' => 'Produk tanpa order',
            'slug' => 'produk-tanpa-order',
            'status' => 'active',
            'visibility' => 'public',
            'price' => 100000,
        ]);

        $this->assertSame([], app(RecentPurchaseService::class)->getRecentPurchasesForProduct($product->id));
    }

    public function test_database_seeder_refuses_to_create_demo_accounts_in_production(): void
    {
        $originalEnvironment = app()->environment();
        app()->instance('env', 'production');

        try {
            $this->expectException(LogicException::class);
            app(DatabaseSeeder::class)->run();
        } finally {
            app()->instance('env', $originalEnvironment);
        }
    }

    public function test_mock_license_validation_is_unavailable_in_production(): void
    {
        $originalEnvironment = app()->environment();
        app()->instance('env', 'production');

        try {
            $this->expectException(LogicException::class);
            app(ApplicationLicenseValidatorService::class)->validateRemote();
        } finally {
            app()->instance('env', $originalEnvironment);
        }
    }

    public function test_payment_webhook_without_active_secret_is_rejected(): void
    {
        $hook = Mockery::mock(PostPaymentHookCoordinator::class);
        $hook->shouldNotReceive('execute');
        $controller = new PaymentGatewayWebhookController($hook);
        $request = Request::create('/api/webhook/payments/midtrans', 'POST', [
            'transaction_id' => 'txn-unsafe',
            'transaction_status' => 'settlement',
            'order_id' => 'INV-UNSAFE',
            'gross_amount' => '100000.00',
        ]);

        $response = $controller($request, 'midtrans');

        $this->assertSame(401, $response->getStatusCode());
    }
}
