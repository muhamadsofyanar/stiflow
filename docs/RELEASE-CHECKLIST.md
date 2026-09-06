# Voucher MVP Release Checklist

**Candidate date:** 6 September 2026  
**Scope:** verified promoter → voucher order → manual transfer proof → staff approval → single STIFIN write → success or human reconciliation

## Automated evidence

Run from the application root:

```bash
vendor/bin/phpunit \
  tests/Feature/Integrations/StifinApiClientTest.php \
  tests/Feature/PromoterRemoteVerificationTest.php \
  tests/Feature/Fase2/Fase2_VER1_PromotorVerifyPermissionTest.php \
  tests/Feature/VoucherCheckoutTest.php \
  tests/Feature/VoucherOrderServiceTest.php \
  tests/Feature/PaymentProofAccessTest.php \
  tests/Feature/PaymentVerificationServiceTest.php \
  tests/Feature/PaymentVerifyRaceConditionTest.php \
  tests/Feature/OutboxDispatchPolicyTest.php \
  tests/Feature/VoucherFulfillmentAmbiguousTest.php \
  tests/Feature/VoucherIdempotencyTest.php \
  tests/Feature/FullVoucherFlowTest.php \
  tests/Feature/FreshMigrationSchemaTest.php \
  tests/Feature/ProductionRuntimeFilesTest.php \
  tests/Feature/ProductionSafetyTest.php \
  tests/Feature/Fase2/Fase2_ACPO3_CommissionZeroVoucherTest.php
```

Result recorded in this workspace: **43 tests and 163 assertions pass**. Re-run on the release image and attach the raw output before approval.

| Check | Workspace result | Release-host result |
|---|---:|---:|
| Voucher MVP focused tests | Pass | Pending |
| PHP syntax scan | Pass | Pending |
| Vite production build | Pass | Pending |
| Runtime smoke script | Pass | Pending |
| Fresh SQLite migration | Pass | Not a production gate |
| Fresh MySQL 8 migration | Not run | Pending |
| Docker image build | Not run | Pending |
| Compose web/worker/scheduler | Not run | Pending |
| Backup restore into new instance | Not run | Pending |
| Full imported test suite | Fail: 29 errors, 14 failures | Must remain excluded/disabled |

## Docker host commands

```bash
docker compose -f compose.production.yaml config
docker build -t stiflow:voucher-mvp .
docker run --rm stiflow:voucher-mvp docker/runtime-smoke.sh
docker compose -f compose.production.yaml up -d db
docker compose -f compose.production.yaml run --rm app php artisan migrate:fresh --force
docker compose -f compose.production.yaml up -d app worker scheduler
curl --fail http://127.0.0.1:8080/up
docker compose -f compose.production.yaml exec app php artisan migrate:status
```

Do not use `migrate:fresh` against a database containing real data.

## Controlled pilot matrix

Use test promoter and branch accounts approved for staging. Never retry an uncertain STIFIN POST. Record evidence before resolving a reconciliation case.

| Scenario | Order ID | Operation reference | Balance before/after | Local result | Remote result | Reconciliation | Operator | Result |
|---|---|---|---|---|---|---|---|---|
| Quantity 1 |  |  |  |  |  | N/A |  | Pending |
| Quantity 5 |  |  |  |  |  | N/A |  | Pending |
| Allowed custom quantity |  |  |  |  |  | N/A |  | Pending |
| Rejected payment proof |  | N/A | N/A |  | N/A | N/A |  | Pending |
| Wrong promoter code |  | N/A | N/A |  |  | N/A |  | Pending |
| STIFIN timeout after POST |  |  |  | `needs_review` | uncertain | Required |  | Pending |
| Two concurrent dispatchers |  |  |  |  | one POST maximum | As applicable |  | Pending |

## Go/no-go

Release remains **NO-GO** until all release-host and pilot rows pass, real API response fixtures are confirmed, restore succeeds, and an operator signs below.

- Branch owner: ____________________ Date: __________
- Technical operator: ______________ Date: __________
- Restore witness: __________________ Date: __________
