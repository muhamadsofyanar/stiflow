<?php

namespace Tests\Feature\Fase2;

use App\Enums\ProductLicenseKeyStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductLicenseKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_ACI2_LicenseValidationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_license_validate_valid_key_returns_true(): void
    {
        $product = Product::factory()->create(['type' => 'digital']);
        $license = ProductLicenseKey::query()->create([
            'product_id' => $product->id,
            'license_key' => 'VALID-KEY-I2-TEST',
            'status' => ProductLicenseKeyStatus::Active,
            'max_activations' => 5,
        ]);

        $response = $this->postJson(route('api.license.validate'), [
            'license_key' => 'VALID-KEY-I2-TEST',
            'hardware_id' => 'hwid-test-i2',
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['valid' => true, 'code' => 'VALID']);
    }

    public function test_api_license_validate_invalid_key_returns_404(): void
    {
        $response = $this->postJson(route('api.license.validate'), [
            'license_key' => 'NOT-EXIST-KEY',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['valid' => false, 'code' => 'LICENSE_NOT_FOUND']);
    }

    public function test_web_license_validate_route_works(): void
    {
        $product = Product::factory()->create(['type' => 'digital']);
        $license = ProductLicenseKey::query()->create([
            'product_id' => $product->id,
            'license_key' => 'WEB-LIC-I2',
            'status' => ProductLicenseKeyStatus::Active,
            'max_activations' => 1,
        ]);

        $response = $this->post(route('license.validate.web'), [
            'license_key' => 'WEB-LIC-I2',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['valid' => true]);
    }
}
