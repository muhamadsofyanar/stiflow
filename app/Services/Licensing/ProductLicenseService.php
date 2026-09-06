<?php

namespace App\Services\Licensing;

use App\Enums\AuditAction;
use App\Enums\ProductLicenseKeyStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductLicenseActivation;
use App\Models\ProductLicenseKey;
use App\Models\ProductLicenseValidationLog;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class ProductLicenseService
{
    public function bulkGenerate(
        Product $product,
        int $count,
        User $importer,
        array $options = [],
    ): array {
        if ($count <= 0) {
            throw new InvalidArgumentException('Jumlah generate harus lebih dari 0.');
        }
        if ($count > 10000) {
            throw new InvalidArgumentException('Maksimal 10.000 keys per batch.');
        }

        $batchRef = 'IMPORT-' . date('Ymd-His') . '-' . Str::random(6);
        $maxActivations = (int) ($options['max_activations'] ?? 1);
        $variantId = $options['variant_id'] ?? null;
        $expiresAt = $options['expires_at'] ?? null;
        $featuresJson = $options['features_json'] ?? null;

        return DB::transaction(function () use (
            $product,
            $count,
            $importer,
            $batchRef,
            $maxActivations,
            $variantId,
            $expiresAt,
            $featuresJson,
        ) {
            $keys = [];
            $now = now();

            for ($i = 0; $i < $count; $i++) {
                $plain = $this->createPlainText($product, $variantId);
                $fingerprint = hash('sha256', $plain);

                $key = ProductLicenseKey::query()->create([
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'encrypted_key_value' => $plain,
                    'fingerprint_sha256' => $fingerprint,
                    'status' => ProductLicenseKeyStatus::Available,
                    'max_activations' => $maxActivations,
                    'activation_count' => 0,
                    'assigned_to_user_id' => null,
                    'assigned_from_order_item_id' => null,
                    'assigned_from_order_id' => null,
                    'assigned_at' => null,
                    'expires_at' => $expiresAt,
                    'suspended_at' => null,
                    'suspension_reason' => null,
                    'revoked_at' => null,
                    'revocation_reason' => null,
                    'import_batch_ref' => $batchRef,
                    'imported_by_user_id' => $importer->id,
                    'features_json' => $featuresJson,
                    'product_signing_secret_fingerprint' => null,
                ]);

                $keys[] = $key;
            }

            AuditService::record(
                action: AuditAction::ProductCreated ?? 'license_keys.generated',
                subject: $product,
                after: [
                    'count' => $count,
                    'batch_ref' => $batchRef,
                    'max_activations' => $maxActivations,
                ],
                actor: $importer,
            );

            return $keys;
        }, 3);
    }

    public function assignToOrderItem(
        OrderItem $orderItem,
        User $assigneeUser,
    ): ProductLicenseKey {
        return DB::transaction(function () use ($orderItem, $assigneeUser) {
            $order = $orderItem->order;
            $snapshot = $orderItem->product_snapshot_json;
            $productId = $snapshot['product_id'] ?? null;
            $variantId = $snapshot['variant_id'] ?? null;

            $query = ProductLicenseKey::query()
                ->where('status', ProductLicenseKeyStatus::Available)
                ->lockForUpdate();

            if ($productId) {
                $query->where('product_id', $productId);
            }
            if ($variantId) {
                $query->where('variant_id', $variantId);
            }

            $key = $query->orderBy('id', 'asc')->first();
            if (! $key) {
                throw new RuntimeException('Tidak ada license key yang tersedia untuk produk ini.');
            }

            $key->status = ProductLicenseKeyStatus::Assigned;
            $key->assigned_to_user_id = $assigneeUser->id;
            $key->assigned_from_order_item_id = $orderItem->id;
            $key->assigned_from_order_id = $order->id;
            $key->assigned_at = now();
            $key->save();

            AuditService::record(
                action: AuditAction::ProductUpdated ?? 'license_key.assigned',
                subject: $key,
                after: [
                    'user_id' => $assigneeUser->id,
                    'order_item_id' => $orderItem->id,
                    'order_id' => $order->id,
                ],
            );

            return $key;
        });
    }

    public function activateFingerprint(
        ProductLicenseKey $key,
        string $deviceFingerprint,
        array $context = [],
    ): ProductLicenseActivation {
        return DB::transaction(function () use ($key, $deviceFingerprint, $context) {
            if (! in_array($key->status, [ProductLicenseKeyStatus::Assigned, ProductLicenseKeyStatus::Active], true)) {
                throw new RuntimeException('License key tidak dalam status yang dapat diaktifkan.');
            }

            if ($key->expires_at !== null && $key->expires_at->isPast()) {
                throw new RuntimeException('License key sudah kadaluarsa.');
            }

            $existing = ProductLicenseActivation::query()
                ->where('product_license_key_id', $key->id)
                ->where('device_fingerprint_sha256', hash('sha256', $deviceFingerprint))
                ->first();

            if ($existing) {
                $existing->last_verified_at = now();
                $existing->save();

                return $existing;
            }

            if ($key->max_activations > 0 && (int) $key->activation_count >= (int) $key->max_activations) {
                throw new RuntimeException('Batas aktivasi sudah tercapai.');
            }

            $activation = ProductLicenseActivation::query()->create([
                'product_license_key_id' => $key->id,
                'device_fingerprint_sha256' => hash('sha256', $deviceFingerprint),
                'device_label' => $context['device_label'] ?? null,
                'activated_at' => now(),
                'last_verified_at' => now(),
                'revoked_at' => null,
                'activation_ip' => $context['ip_address'] ?? null,
                'activation_meta_json' => $context['meta'] ?? null,
            ]);

            $key->activation_count = (int) $key->activation_count + 1;
            $key->status = ProductLicenseKeyStatus::Active;
            $key->save();

            return $activation;
        });
    }

    public function validateEndpoint(array $payload): array
    {
        $licenseKeyValue = $payload['license_key'] ?? $payload['key'] ?? null;
        $fingerprint = $payload['device_fingerprint'] ?? $payload['fingerprint'] ?? null;

        $log = new ProductLicenseValidationLog();
        $log->requested_at = now();
        $log->request_payload_json = $payload;
        $log->request_ip = request()?->ip();

        if (! $licenseKeyValue) {
            $log->is_valid = false;
            $log->validation_result_json = ['valid' => false, 'reason' => 'missing_key'];
            $log->save();

            return ['valid' => false, 'reason' => 'missing_key'];
        }

        $fingerprintSha = $fingerprint ? hash('sha256', $fingerprint) : null;
        $fingerprintLookup = hash('sha256', $licenseKeyValue);

        $key = ProductLicenseKey::query()
            ->where('fingerprint_sha256', $fingerprintLookup)
            ->first();

        if (! $key) {
            $log->is_valid = false;
            $log->validation_result_json = ['valid' => false, 'reason' => 'unknown_key'];
            $log->save();

            return ['valid' => false, 'reason' => 'unknown_key'];
        }

        $log->product_license_key_id = $key->id;

        $invalidStatuses = [
            ProductLicenseKeyStatus::Revoked,
            ProductLicenseKeyStatus::Suspended,
            ProductLicenseKeyStatus::Available,
        ];
        if (in_array($key->status, $invalidStatuses, true)) {
            $log->is_valid = false;
            $log->validation_result_json = ['valid' => false, 'reason' => 'invalid_status', 'status' => $key->status->value];
            $log->save();

            return ['valid' => false, 'reason' => 'invalid_status', 'status' => $key->status->value];
        }

        if ($key->expires_at !== null && $key->expires_at->isPast()) {
            $log->is_valid = false;
            $log->validation_result_json = ['valid' => false, 'reason' => 'expired'];
            $log->save();

            return ['valid' => false, 'reason' => 'expired'];
        }

        if ($fingerprintSha !== null) {
            $activation = ProductLicenseActivation::query()
                ->where('product_license_key_id', $key->id)
                ->where('device_fingerprint_sha256', $fingerprintSha)
                ->whereNull('revoked_at')
                ->first();

            if (! $activation) {
                $log->is_valid = false;
                $log->validation_result_json = ['valid' => false, 'reason' => 'fingerprint_not_activated'];
                $log->save();

                return ['valid' => false, 'reason' => 'fingerprint_not_activated'];
            }

            $activation->last_verified_at = now();
            $activation->save();
        }

        $log->is_valid = true;
        $log->validation_result_json = [
            'valid' => true,
            'product_id' => $key->product_id,
            'features' => $key->features_json,
            'expires_at' => $key->expires_at?->toIso8601String(),
        ];
        $log->save();

        return [
            'valid' => true,
            'product_id' => $key->product_id,
            'variant_id' => $key->variant_id,
            'features' => $key->features_json,
            'expires_at' => $key->expires_at?->toIso8601String(),
            'assigned_to_user_id' => $key->assigned_to_user_id,
        ];
    }

    private function createPlainText(Product $product, $variantId): string
    {
        $prefix = Str::upper(Str::limit(preg_replace('/[^A-Z0-9]/i', '', $product->name ?? 'LICENSE'), 4, ''));
        $variant = '';
        if ($variantId) {
            $variantObj = ProductVariant::query()->find($variantId);
            if ($variantObj) {
                $variant = '-' . Str::upper(Str::limit(preg_replace('/[^A-Z0-9]/i', '', $variantObj->name ?? ''), 3, ''));
            }
        }
        $random = strtoupper(Str::random(20));
        $parts = str_split($random, 5);

        return sprintf('%s%s-%s', $prefix, $variant, implode('-', $parts));
    }
}
