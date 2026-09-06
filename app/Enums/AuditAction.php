<?php

namespace App\Enums;

enum AuditAction: string
{
    case Login = 'auth.login';
    case Logout = 'auth.logout';
    case PasswordChanged = 'auth.password_changed';

    case PromoterCreated = 'promoter.created';
    case PromoterUpdated = 'promoter.updated';
    case PromoterVerified = 'promoter.verified';
    case PromoterRejected = 'promoter.rejected';
    case PromoterStifinCodeChanged = 'promoter.stifin_code_changed';

    case OrderCreated = 'order.created';
    case OrderStatusChanged = 'order.status_changed';
    case OrderExpired = 'order.expired';

    case PaymentProofSubmitted = 'payment.proof_submitted';
    case PaymentApproved = 'payment.approved';
    case PaymentRejected = 'payment.rejected';
    case PaymentRefunded = 'payment.refunded';

    case VoucherFulfillmentStarted = 'voucher.fulfillment_started';
    case VoucherFulfillmentSuccess = 'voucher.fulfillment_success';
    case VoucherFulfillmentNeedsReview = 'voucher.fulfillment_needs_review';
    case VoucherFulfillmentFailed = 'voucher.fulfillment_failed';

    case ReconciliationResolved = 'reconciliation.resolved';
    case ReconciliationCreated = 'reconciliation.created';

    case BranchSettingUpdated = 'setting.branch_updated';
    case BrandSettingUpdated = 'setting.brand_updated';

    case ProductCreated = 'product.created';
    case ProductUpdated = 'product.updated';
    case ProductDeleted = 'product.deleted';
}
