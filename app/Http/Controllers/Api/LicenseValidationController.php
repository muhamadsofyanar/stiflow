<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProductLicenseKeyStatus;
use App\Http\Controllers\Controller;
use App\Models\ProductLicenseActivation;
use App\Models\ProductLicenseKey;
use App\Models\ProductLicenseValidationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseValidationController extends Controller
{
    public function validate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'license_key' => 'required|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'hardware_id' => 'nullable|string|max:255',
            'client_version' => 'nullable|string|max:100',
        ]);

        $license = ProductLicenseKey::query()
            ->where('license_key', $validated['license_key'])
            ->first();

        if (! $license) {
            $this->logValidation($validated, false, 'LICENSE_NOT_FOUND');

            return response()->json([
                'valid' => false,
                'code' => 'LICENSE_NOT_FOUND',
                'message' => 'License key tidak ditemukan.',
            ], 404);
        }

        if ($validated['product_id'] && $license->product_id !== (int) $validated['product_id']) {
            $this->logValidation($validated, false, 'PRODUCT_MISMATCH', $license);

            return response()->json([
                'valid' => false,
                'code' => 'PRODUCT_MISMATCH',
                'message' => 'License key tidak sesuai untuk produk ini.',
            ], 400);
        }

        if ($license->status === ProductLicenseKeyStatus::Revoked) {
            $this->logValidation($validated, false, 'LICENSE_REVOKED', $license);

            return response()->json([
                'valid' => false,
                'code' => 'LICENSE_REVOKED',
                'message' => 'License key telah dicabut.',
            ], 403);
        }

        if ($license->status === ProductLicenseKeyStatus::Expired) {
            $this->logValidation($validated, false, 'LICENSE_EXPIRED', $license);

            return response()->json([
                'valid' => false,
                'code' => 'LICENSE_EXPIRED',
                'message' => 'License key sudah kedaluwarsa.',
            ], 403);
        }

        if ($license->expires_at && $license->expires_at->isPast()) {
            $license->update(['status' => ProductLicenseKeyStatus::Expired]);
            $this->logValidation($validated, false, 'LICENSE_EXPIRED', $license);

            return response()->json([
                'valid' => false,
                'code' => 'LICENSE_EXPIRED',
                'message' => 'License key sudah kedaluwarsa.',
            ], 403);
        }

        if ($validated['hardware_id']) {
            $maxActivations = $license->max_activations ?? 1;
            $currentActivations = ProductLicenseActivation::query()
                ->where('product_license_key_id', $license->id)
                ->count();

            $existingActivation = ProductLicenseActivation::query()
                ->where('product_license_key_id', $license->id)
                ->where('hardware_id', $validated['hardware_id'])
                ->first();

            if (! $existingActivation && $currentActivations >= $maxActivations) {
                $this->logValidation($validated, false, 'MAX_ACTIVATIONS_REACHED', $license);

                return response()->json([
                    'valid' => false,
                    'code' => 'MAX_ACTIVATIONS_REACHED',
                    'message' => 'Batas aktivasi license terlampaui.',
                ], 403);
            }

            if (! $existingActivation) {
                ProductLicenseActivation::query()->create([
                    'product_license_key_id' => $license->id,
                    'hardware_id' => $validated['hardware_id'],
                    'activated_at' => now(),
                    'client_ip' => $request->ip(),
                    'client_version' => $validated['client_version'] ?? null,
                ]);

                if ($license->status === ProductLicenseKeyStatus::Issued) {
                    $license->update(['status' => ProductLicenseKeyStatus::Active]);
                }
            } else {
                $existingActivation->touch();
            }
        }

        $this->logValidation($validated, true, 'VALID', $license);

        return response()->json([
            'valid' => true,
            'code' => 'VALID',
            'message' => 'License valid.',
            'license' => [
                'id' => $license->id,
                'product_id' => $license->product_id,
                'status' => $license->status?->value ?? $license->status,
                'expires_at' => $license->expires_at?->toIso8601String(),
                'max_activations' => $license->max_activations,
            ],
        ]);
    }

    public function validateWeb(Request $request): JsonResponse
    {
        return $this->validate($request);
    }

    private function logValidation(array $input, bool $valid, string $code, ?ProductLicenseKey $license = null): void
    {
        ProductLicenseValidationLog::query()->create([
            'product_license_key_id' => $license?->id,
            'license_key_input' => $input['license_key'],
            'product_id' => $input['product_id'] ?? null,
            'hardware_id' => $input['hardware_id'] ?? null,
            'client_version' => $input['client_version'] ?? null,
            'client_ip' => request()->ip(),
            'is_valid' => $valid,
            'result_code' => $code,
            'validated_at' => now(),
        ]);
    }
}
