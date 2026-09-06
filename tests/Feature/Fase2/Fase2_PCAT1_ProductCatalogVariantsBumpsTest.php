<?php

namespace Tests\Feature\Fase2;

use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Fase2_PCAT1_ProductCatalogVariantsBumpsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_product_catalog_and_variant_crud(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin, 'status' => 'active', 'email_verified_at' => now(),
        ]);

        $productStore = $this->actingAs($admin)->post(route('admin.products-catalog.store'), [
            'name' => 'Kursus Premium STIFIN',
            'slug' => 'kursus-premium-pcat1',
            'type' => ProductType::Course->value,
            'base_price' => 1500000,
            'is_active' => true,
        ]);
        $productStore->assertRedirect(route('admin.products-catalog.index'));

        $product = Product::query()->where('slug', 'kursus-premium-pcat1')->firstOrFail();

        $variantStore = $this->actingAs($admin)->post(route('admin.variants.store'), [
            'product_id' => $product->id,
            'name' => 'Variant Basic',
            'sku' => 'SKU-PCAT1-BASIC',
            'price' => 500000,
            'stock' => 100,
            'is_active' => true,
        ]);
        $variantStore->assertRedirect(route('admin.variants.index'));
        $this->assertDatabaseHas('product_variants', ['sku' => 'SKU-PCAT1-BASIC']);

        $this->actingAs($admin)->get(route('admin.products-catalog.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.variants.index'))->assertStatus(200);
    }
}
