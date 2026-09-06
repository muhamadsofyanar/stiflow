<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FreshMigrationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_deferred_foreign_key_columns_exist_after_fresh_migration(): void
    {
        $this->assertTrue(Schema::hasColumn('orders', 'coupon_id'));
        $this->assertTrue(Schema::hasColumn('courses', 'cover_image_id'));
        $this->assertTrue(Schema::hasColumn('lessons', 'digital_asset_id'));
        $this->assertTrue(Schema::hasColumn('campaigns', 'sender_integration_connection_id'));
        $this->assertTrue(Schema::hasColumn('message_deliveries', 'integration_connection_id'));
    }

    public function test_constraints_are_declared_only_after_referenced_tables_exist(): void
    {
        $migrationDirectory = database_path('migrations');
        $orders = file_get_contents($migrationDirectory.'/0001_01_01_000007_create_orders_table.php');
        $lms = file_get_contents($migrationDirectory.'/0001_01_01_000025_create_lms_tables.php');
        $communication = file_get_contents($migrationDirectory.'/0001_01_01_000029_create_communication_tables.php');
        $deferred = file_get_contents($migrationDirectory.'/0001_01_01_000044_add_deferred_foreign_keys.php');

        $this->assertStringNotContainsString("foreignId('coupon_id')->nullable()->constrained", $orders);
        $this->assertStringNotContainsString("foreignId('cover_image_id')->nullable()->constrained", $lms);
        $this->assertStringNotContainsString("foreignId('digital_asset_id')->nullable()->constrained", $lms);
        $this->assertStringNotContainsString("foreignId('sender_integration_connection_id')->nullable()->constrained", $communication);
        $this->assertStringNotContainsString("foreignId('integration_connection_id')->nullable()->constrained", $communication);

        foreach (['orders', 'courses', 'lessons', 'campaigns', 'message_deliveries'] as $table) {
            $this->assertStringContainsString("Schema::table('{$table}'", $deferred);
        }
    }

    public function test_payouts_table_is_created_before_commission_entries_foreign_key(): void
    {
        $migration = file_get_contents(
            database_path('migrations/0001_01_01_000023_create_commission_tables.php'),
        );

        $payoutsPosition = strpos($migration, "Schema::create('payouts'");
        $entriesPosition = strpos($migration, "Schema::create('commission_entries'");

        $this->assertIsInt($payoutsPosition);
        $this->assertIsInt($entriesPosition);
        $this->assertLessThan($entriesPosition, $payoutsPosition);
        $this->assertStringContainsString(
            "foreignId('payout_id')->nullable()->constrained()->nullOnDelete()",
            $migration,
        );

        $dropEntriesPosition = strpos($migration, "Schema::dropIfExists('commission_entries')");
        $dropPayoutsPosition = strpos($migration, "Schema::dropIfExists('payouts')");

        $this->assertIsInt($dropEntriesPosition);
        $this->assertIsInt($dropPayoutsPosition);
        $this->assertLessThan($dropPayoutsPosition, $dropEntriesPosition);
    }

    public function test_product_variant_migration_does_not_position_columns_after_missing_order_item_columns(): void
    {
        $migration = file_get_contents(
            database_path('migrations/0001_01_01_000024_create_product_variants_and_bumps.php'),
        );

        $this->assertStringNotContainsString("after('product_id')", $migration);
        $this->assertStringNotContainsString("after('snapshot_price_per_unit')", $migration);
    }

    public function test_integration_connections_composite_index_uses_mysql_safe_name(): void
    {
        $index = collect(Schema::getIndexes('integration_connections'))
            ->first(fn (array $index): bool => $index['columns'] === [
                'provider_category',
                'provider_type',
                'is_active',
            ]);

        $this->assertNotNull($index);
        $this->assertSame('integration_connections_provider_lookup_idx', $index['name']);
        $this->assertLessThanOrEqual(64, strlen($index['name']));
    }
}
