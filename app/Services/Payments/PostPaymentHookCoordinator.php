<?php

namespace App\Services\Payments;

use App\Enums\EnrollmentSource;
use App\Enums\ProductType;
use App\Jobs\CommissionProcessJob;
use App\Models\Contact;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ReferralLink;
use App\Services\Affiliate\CommissionCalculationService;
use App\Services\Licensing\ProductLicenseService;
use App\Services\Lms\EnrollmentProgressService;
use App\Services\Points\PointLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostPaymentHookCoordinator
{
    public function __construct(
        private readonly EnrollmentProgressService $enrollmentService,
        private readonly ProductLicenseService $licenseService,
        private readonly PointLedgerService $pointLedgerService,
        private readonly CommissionCalculationService $commissionService,
    ) {
    }

    public function execute(Order $order): array
    {
        if (! $order->isPaidOrLater()) {
            return [
                'skipped' => true,
                'reason' => 'order_not_paid',
            ];
        }

        $results = [
            'order_id' => $order->id,
            'enrollments' => [],
            'licenses' => [],
            'commissions_dispatched' => false,
            'points' => [],
            'contact_created' => false,
            'referral_attributed' => false,
        ];

        try {
            DB::beginTransaction();

            $items = OrderItem::query()->where('order_id', $order->id)->get();

            $allCommissionEligible = true;

            foreach ($items as $item) {
                $snapshot = $item->product_snapshot_json ?? [];
                $productId = $snapshot['product_id'] ?? null;
                $productType = $snapshot['product_type'] ?? null;
                $commissionEligible = $snapshot['commission_eligible'] ?? null;
                $pointsEligible = $snapshot['points_eligible'] ?? null;

                if ($productType === ProductType::Voucher->value) {
                    $allCommissionEligible = false;
                }
                if ($commissionEligible === false || $commissionEligible === 0 || $commissionEligible === '0') {
                    $allCommissionEligible = false;
                }

                $liveProduct = $productId ? Product::query()->find($productId) : null;
                if ($liveProduct && (! $liveProduct->commission_eligible)) {
                    $allCommissionEligible = false;
                }

                $results['enrollments'][] = $this->handleEnrollment($order, $item, $productType, $productId);
                $results['licenses'][] = $this->handleLicense($order, $item, $productType, $productId);
                $results['points'][] = $this->handlePoints($order, $item, $productType, $pointsEligible, $liveProduct);
            }

            if ($allCommissionEligible && $items->isNotEmpty()) {
                $firstItem = $items->first();
                $snapshot = $firstItem->product_snapshot_json ?? [];
                $productType = $snapshot['product_type'] ?? null;
                $liveProduct = isset($snapshot['product_id']) ? Product::query()->find($snapshot['product_id']) : null;

                $voucherNoCommission = false;
                if ($productType === ProductType::Voucher->value) {
                    if ($liveProduct && $liveProduct->commission_eligible === false) {
                        $voucherNoCommission = true;
                    } elseif (! isset($snapshot['commission_eligible'])) {
                        $voucherNoCommission = true;
                    }
                }

                if (! $voucherNoCommission) {
                    CommissionProcessJob::dispatch($order->id)->delay(now()->addSeconds(10));
                    $results['commissions_dispatched'] = true;
                }
            }

            $contactResult = $this->handleContactAndReferral($order);
            $results['contact_created'] = $contactResult['contact_created'];
            $results['referral_attributed'] = $contactResult['referral_attributed'];

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PostPaymentHookCoordinator failed for order ' . $order->id . ': ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    private function handleEnrollment(Order $order, OrderItem $item, ?string $productType, $productId): array
    {
        if (! in_array($productType, [
            ProductType::Membership->value,
            ProductType::Course->value,
            'lesson_bundle',
            'LessonBundle',
        ], true)) {
            return ['status' => 'skipped', 'reason' => 'not_enrollment_type'];
        }

        if (! $order->user_id) {
            return ['status' => 'skipped', 'reason' => 'no_user'];
        }

        try {
            $course = null;
            if ($productId) {
                $course = Course::query()
                    ->where('product_id', $productId)
                    ->orWhere('id', $productId)
                    ->first();
            }

            if (! $course) {
                return ['status' => 'skipped', 'reason' => 'course_not_found'];
            }

            $user = $order->user;
            if (! $user) {
                return ['status' => 'skipped', 'reason' => 'user_not_found'];
            }

            $enrollment = $this->enrollmentService->createEnrollment(
                user: $user,
                course: $course,
                source: EnrollmentSource::Order,
                metadata: ['order_id' => $order->id],
            );

            return ['status' => 'created', 'enrollment_id' => $enrollment->id];
        } catch (\Throwable $e) {
            Log::warning('PostPayment enrollment error: ' . $e->getMessage());

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function handleLicense(Order $order, OrderItem $item, ?string $productType, $productId): array
    {
        $liveProduct = $productId ? Product::query()->find($productId) : null;
        $isLicenseEnabled = $productType === 'license'
            || $productType === 'LicenseEnabled'
            || ($liveProduct && ($liveProduct->type?->value === 'license' || $liveProduct->type === 'license'));

        if (! $isLicenseEnabled) {
            return ['status' => 'skipped', 'reason' => 'not_license_enabled'];
        }

        try {
            if (method_exists($this->licenseService, 'assignAvailable')) {
                $assigned = $this->licenseService->assignAvailable($order, $item);

                return ['status' => 'assigned', 'result' => is_array($assigned) ? count($assigned) : 1];
            }

            return ['status' => 'skipped', 'reason' => 'assignAvailable_method_not_found'];
        } catch (\Throwable $e) {
            Log::warning('PostPayment license error: ' . $e->getMessage());

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function handlePoints(Order $order, OrderItem $item, ?string $productType, $pointsEligible, $liveProduct): array
    {
        if ($productType === ProductType::Voucher->value) {
            return ['status' => 'skipped', 'reason' => 'voucher_no_points'];
        }

        if ($pointsEligible === false || $pointsEligible === 0 || $pointsEligible === '0') {
            return ['status' => 'skipped', 'reason' => 'points_not_eligible_snapshot'];
        }

        if ($liveProduct && ! $liveProduct->points_eligible) {
            return ['status' => 'skipped', 'reason' => 'points_not_eligible_product'];
        }

        try {
            $entry = $this->pointLedgerService->earnFromOrderItem($order, $item);

            return ['status' => $entry ? 'earned' : 'skipped', 'entry_id' => $entry?->id];
        } catch (\Throwable $e) {
            Log::warning('PostPayment points error: ' . $e->getMessage());

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function handleContactAndReferral(Order $order): array
    {
        $result = ['contact_created' => false, 'referral_attributed' => false];

        try {
            $billing = $order->referral_snapshot_json ?? [];
            $email = $billing['email'] ?? ($order->user?->email ?? null);
            $name = $billing['name'] ?? ($order->user?->name ?? null);
            $phone = $billing['phone'] ?? ($order->user?->phone ?? null);

            if (! $email && ! $phone) {
                return $result;
            }

            $ownerUserId = $order->user_id;

            $contact = null;
            if ($email) {
                $contact = Contact::query()->where('email', $email)->first();
            }
            if (! $contact && $phone) {
                $contact = Contact::query()->where('phone', $phone)->first();
            }

            if (! $contact) {
                $contact = Contact::query()->create([
                    'owner_user_id' => $ownerUserId,
                    'first_name' => $name ? explode(' ', $name, 2)[0] : null,
                    'last_name' => $name && str_contains($name, ' ') ? explode(' ', $name, 2)[1] : null,
                    'email' => $email,
                    'phone' => $phone,
                    'status' => \App\Enums\ContactStatus::New,
                    'source' => 'checkout',
                ]);
                $result['contact_created'] = true;
            }

            $promoCode = $order->promotor_code_snapshot;
            if ($promoCode && $contact) {
                $referralLink = ReferralLink::query()
                    ->whereHas('promoterProfile', function ($q) use ($promoCode) {
                        $q->where('stifin_code', $promoCode);
                    })
                    ->first();

                if ($referralLink) {
                    if (method_exists($referralLink, 'contacts')) {
                        $alreadyAttached = $referralLink->contacts()->where('contact_id', $contact->id)->exists();
                        if (! $alreadyAttached) {
                            $referralLink->contacts()->attach($contact->id, [
                                'attributed_at' => now(),
                                'source' => 'order_' . $order->id,
                            ]);
                        }
                    }
                    $result['referral_attributed'] = true;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('PostPayment contact/referral error: ' . $e->getMessage());
        }

        return $result;
    }
}
