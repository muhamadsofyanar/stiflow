# Voucher MVP Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver a branch pilot in which an authorized manual-payment approval credits vouchers once to a remotely verified promoter and sends every uncertain write to reconciliation.

**Architecture:** Keep payment approval, outbox creation, and fulfillment as separate transaction boundaries. All STIFIN traffic passes through one adapter with explicit result classification; database uniqueness backs application-level idempotency. Later commerce, CRM, LMS, gateway, licensing, and white-label modules remain outside this release.

**Tech Stack:** PHP 8.2+, Laravel 12, Livewire 3, PHPUnit 11, MySQL 8, database queue, Docker/Coolify.

## Global Constraints

- One deployment and database per branch.
- Voucher target is the authenticated promoter's verified STIFIN code.
- Manual transfer plus admin approval is the only enabled payment path.
- Voucher POST uncertainty is never retried automatically.
- TLS verification is always enabled.
- Tests are written and observed failing before production-code changes.
- The imported source has no `.git` directory; create commits only after the owner initializes the product repository.

---

### Task 1: Correct the STIFIN transport contract

**Files:**
- Create: `tests/Feature/Integrations/StifinApiClientTest.php`
- Modify: `.env.example`
- Modify: `config/services.php`
- Modify: `app/Integrations/Stifin/StifinApiCredentialResolver.php`
- Modify: `app/Integrations/Stifin/StifinApiClient.php`

**Interfaces:**
- Consumes: environment values `STIFIN_API_BASE_URL`, `STIFIN_AUTH_HEADER`, `STIFIN_AUTH_VALUE`, `STIFIN_USER_ID`.
- Produces: `StifinApiClient::addVoucher(string $branchCode, array $payload): StifinOperationResult` using JSON and configured authentication.

- [ ] **Step 1: Write failing request-contract tests**

Create tests using Laravel's HTTP fake:

```php
<?php

namespace Tests\Feature\Integrations;

use App\Integrations\Stifin\StifinApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StifinApiClientTest extends TestCase
{
    public function test_add_voucher_sends_json_and_configured_auth_header(): void
    {
        Http::fake(['https://stifin.test/*' => Http::response(['success' => true], 200)]);

        $client = new StifinApiClient(
            baseUrl: 'https://stifin.test/api',
            userIdIdentifier: 'STIFLOW-KHU',
            connectTimeoutSec: 5,
            totalTimeoutSec: 10,
            authHeader: 'Authorization',
            authValue: 'Bearer secret-value',
        );

        $client->addVoucher('KHU', [
            'KodeID' => 'KHU-ABU-02', 'Jumlah' => '1', 'JmlFree' => '0',
            'SaldoJ' => '10', 'SaldoF' => '0', 'Dispos' => 'ORDER-1',
            'Ket' => 'Order 1', 'UserID' => 'STIFLOW-KHU',
        ]);

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://stifin.test/api/voucherPos/editCabVoucher/KHU'
                && $request->hasHeader('Authorization', 'Bearer secret-value')
                && $request->hasHeader('Content-Type', 'application/json')
                && $request['KodeID'] === 'KHU-ABU-02'
                && $request['Jumlah'] === '1';
        });
    }
}
```

- [ ] **Step 2: Run the focused test and confirm the expected failure**

Run:

```bash
php artisan test tests/Feature/Integrations/StifinApiClientTest.php
```

Expected: failure because named constructor arguments `authHeader` and `authValue` do not exist and the current POST uses form encoding.

- [ ] **Step 3: Align configuration names**

Use this service configuration:

```php
'stifin' => [
    'base_url' => env('STIFIN_API_BASE_URL', 'https://apro.stifin.id/api'),
    'auth_header' => env('STIFIN_AUTH_HEADER'),
    'auth_value' => env('STIFIN_AUTH_VALUE'),
    'user_id' => env('STIFIN_USER_ID', 'STIFLOW-SYSTEM'),
    'connect_timeout' => (int) env('STIFIN_CONNECT_TIMEOUT', 15),
    'timeout' => (int) env('STIFIN_TIMEOUT', 30),
],
```

Replace unused example names with:

```dotenv
STIFIN_API_BASE_URL=https://apro.stifin.id/api
STIFIN_AUTH_HEADER=Authorization
STIFIN_AUTH_VALUE=
STIFIN_USER_ID=STIFLOW-SYSTEM
STIFIN_CONNECT_TIMEOUT=15
STIFIN_TIMEOUT=30
```

- [ ] **Step 4: Implement JSON and header transport**

Add nullable `authHeader` and `authValue` constructor parameters, resolve them from database credentials or `services.stifin`, and build the request as follows:

```php
$http = Http::acceptJson()
    ->timeout($this->totalTimeoutSec)
    ->connectTimeout($this->connectTimeoutSec)
    ->withOptions(['verify' => true]);

if ($this->authHeader !== null && $this->authValue !== null) {
    $http = $http->withHeader($this->authHeader, $this->authValue);
}

$response = $method === 'GET'
    ? $http->get($url)
    : $http->asJson()->post($url, $payload);
```

Extend `StifinApiCredentialResolver` to return `auth_header` and `auth_value` from encrypted credentials with environment fallback. Do not log either value.

- [ ] **Step 5: Run focused and voucher tests**

```bash
php artisan test tests/Feature/Integrations/StifinApiClientTest.php tests/Feature/VoucherFulfillmentAmbiguousTest.php tests/Feature/VoucherIdempotencyTest.php tests/Feature/FullVoucherFlowTest.php
```

Expected: all selected tests pass with no warning.

---

### Task 2: Verify promoters against the configured branch

**Files:**
- Create: `app/Services/Promoter/PromoterVerificationService.php`
- Create: `tests/Feature/PromoterRemoteVerificationTest.php`
- Modify: `app/Integrations/Stifin/StifinApiClient.php`
- Modify: `app/Http/Controllers/Admin/PromotorVerifyController.php`
- Modify: `tests/Feature/Fase2/Fase2_VER1_PromotorVerifyPermissionTest.php`

**Interfaces:**
- Consumes: `StifinApiClient::listPromotersOfBranchResult(string): StifinOperationResult`.
- Produces: `PromoterVerificationService::verify(PromoterProfile $profile, User $actor, ?string $notes): PromoterProfile`.

- [ ] **Step 1: Write failing service tests**

Cover three independent behaviors: exact normalized code match verifies; absent code leaves the profile pending; remote failure leaves the profile pending. Construct `StifinOperationResult` fixtures and bind a Mockery client into the container.

```php
$client->shouldReceive('listPromotersOfBranchResult')->once()->with('KHU')->andReturn(
    new StifinOperationResult(true, 200, '[]', [
        ['KodeID' => ' khu-abu-02 ', 'Nama' => 'Abu'],
    ])
);

$verified = app(PromoterVerificationService::class)->verify($profile, $admin, 'checked');
$this->assertSame(PromoterVerificationStatus::Verified, $verified->verification_status);
```

For the absent-code and remote-failure cases, assert `RuntimeException` and then:

```php
$this->assertSame(PromoterVerificationStatus::Pending, $profile->fresh()->verification_status);
```

- [ ] **Step 2: Run tests and confirm verification currently fails open**

```bash
php artisan test tests/Feature/PromoterRemoteVerificationTest.php tests/Feature/Fase2/Fase2_VER1_PromotorVerifyPermissionTest.php
```

Expected: new tests fail because the verification service/result method does not exist and the controller verifies locally.

- [ ] **Step 3: Implement response-preserving promoter reads**

Add this public client method and make the existing list convenience method call it:

```php
public function listPromotersOfBranchResult(string $branchCode): StifinOperationResult
{
    $url = "{$this->baseUrl}/proGetCab/pro/" . urlencode($branchCode);
    return $this->doRequest('GET', $url);
}
```

The service normalizes top-level lists and wrappers `data`, `Data`, and `result`; it matches `mb_strtoupper(trim((string) $row['KodeID']))` against the profile code normalized the same way. An empty or failed response is not verification evidence.

- [ ] **Step 4: Implement fail-closed verification and audit**

Inside a database transaction, lock the profile, recheck uniqueness among other active verified profiles, update `verification_status`, `verified_at`, `verified_by_user_id`, and `verification_notes`, then call:

```php
AuditService::record(
    action: AuditAction::PromoterVerified,
    subject: $profile,
    before: ['verification_status' => $before],
    after: ['verification_status' => PromoterVerificationStatus::Verified->value],
    actor: $actor,
);
```

Change the controller to inject `PromoterVerificationService`, call `verify`, and return a validation error without changing state when remote verification fails.

- [ ] **Step 5: Run focused tests**

```bash
php artisan test tests/Feature/PromoterRemoteVerificationTest.php tests/Feature/Fase2/Fase2_VER1_PromotorVerifyPermissionTest.php tests/Feature/VoucherCheckoutTest.php
```

Expected: all selected tests pass.

---

### Task 3: Repair fresh migration dependencies

**Files:**
- Create: `database/migrations/0001_01_01_000043_add_deferred_foreign_keys.php`
- Create: `tests/Feature/FreshMigrationSchemaTest.php`
- Modify: `database/migrations/0001_01_01_000007_create_orders_table.php`
- Modify: `database/migrations/0001_01_01_000025_create_lms_tables.php`
- Modify: `database/migrations/0001_01_01_000029_create_communication_tables.php`

**Interfaces:**
- Consumes: tables created through migration `000030`.
- Produces: the same model-visible columns with constraints added only after referenced tables exist.

- [ ] **Step 1: Write a failing schema test**

```php
public function test_all_deferred_foreign_key_columns_exist_after_fresh_migration(): void
{
    $this->assertTrue(Schema::hasColumn('orders', 'coupon_id'));
    $this->assertTrue(Schema::hasColumn('courses', 'cover_image_id'));
    $this->assertTrue(Schema::hasColumn('lessons', 'digital_asset_id'));
    $this->assertTrue(Schema::hasColumn('campaigns', 'sender_integration_connection_id'));
    $this->assertTrue(Schema::hasColumn('message_deliveries', 'integration_connection_id'));
}
```

The decisive failure evidence is a clean MySQL migration, because SQLite does not reproduce MySQL's referenced-table ordering rules.

- [ ] **Step 2: Run a clean MySQL migration and capture the first dependency failure**

```bash
php artisan migrate:fresh --database=mysql --force
```

Expected before the fix: failure when `orders` attempts to reference `coupons`, or the next equivalent missing referenced table.

- [ ] **Step 3: Create columns without early constraints**

Use plain nullable unsigned big integers in the three historical migrations:

```php
$table->unsignedBigInteger('coupon_id')->nullable();
$table->unsignedBigInteger('cover_image_id')->nullable();
$table->unsignedBigInteger('digital_asset_id')->nullable();
$table->unsignedBigInteger('sender_integration_connection_id')->nullable();
$table->unsignedBigInteger('integration_connection_id')->nullable();
```

- [ ] **Step 4: Add deferred constraints in migration 000043**

```php
Schema::table('orders', fn (Blueprint $table) =>
    $table->foreign('coupon_id')->references('id')->on('coupons')->nullOnDelete()
);
Schema::table('courses', fn (Blueprint $table) =>
    $table->foreign('cover_image_id')->references('id')->on('digital_assets')->nullOnDelete()
);
Schema::table('lessons', fn (Blueprint $table) =>
    $table->foreign('digital_asset_id')->references('id')->on('digital_assets')->nullOnDelete()
);
Schema::table('campaigns', fn (Blueprint $table) =>
    $table->foreign('sender_integration_connection_id')->references('id')->on('integration_connections')->nullOnDelete()
);
Schema::table('message_deliveries', fn (Blueprint $table) =>
    $table->foreign('integration_connection_id')->references('id')->on('integration_connections')->nullOnDelete()
);
```

The `down()` method drops these five foreign keys in reverse order.

- [ ] **Step 5: Verify fresh migration and rollback**

```bash
php artisan migrate:fresh --database=mysql --force
php artisan migrate:rollback --database=mysql --step=47 --force
php artisan migrate --database=mysql --force
php artisan test tests/Feature/FreshMigrationSchemaTest.php
```

Expected: every command exits 0.

---

### Task 4: Back payment/outbox idempotency with database constraints

**Files:**
- Create: `database/migrations/0001_01_01_000044_add_voucher_outbox_uniqueness.php`
- Modify: `app/Services/Payment/PaymentVerificationService.php`
- Modify: `tests/Feature/PaymentVerifyRaceConditionTest.php`

**Interfaces:**
- Consumes: voucher order with exactly one voucher item in the MVP.
- Produces: one `fulfill_voucher` outbox row keyed by deterministic correlation ID `voucher-order-item:{id}`.

- [ ] **Step 1: Replace the sequential race test with two database connections**

Use two processes or two independent MySQL connections to approve the same proof. After both complete, assert:

```php
$this->assertSame(1, OutboxEvent::query()
    ->where('event_type', 'fulfill_voucher')
    ->where('aggregate_type', Order::class)
    ->where('aggregate_id', $order->id)
    ->count());
```

The existing test calls the service twice sequentially and does not prove concurrency.

- [ ] **Step 2: Run the MySQL concurrency test and observe the missing database guarantee**

```bash
php artisan test tests/Feature/PaymentVerifyRaceConditionTest.php --env=mysql-testing
```

Expected before the fix: duplicate outbox rows or a test assertion showing the schema has no unique event key.

- [ ] **Step 3: Add and use a deterministic unique key**

Add `idempotency_key` to `outbox_events`:

```php
$table->string('idempotency_key', 160)->nullable()->unique()->after('event_type');
```

Replace the existence-check/create pair with:

```php
$orderItem = $order->items()->where('fulfillment_type', 'voucher')->firstOrFail();
$idempotencyKey = 'voucher-order-item:' . $orderItem->id;
$correlationId = 'FUL-' . $orderItem->id;

OutboxEvent::query()->firstOrCreate(
    ['idempotency_key' => $idempotencyKey],
    [
        'event_type' => 'fulfill_voucher',
        'aggregate_type' => Order::class,
        'aggregate_id' => $order->id,
        'payload_json' => [
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'order_number' => $order->number,
            'user_id' => $order->user_id,
            'promotor_code_snapshot' => $order->promotor_code_snapshot,
            'correlation_id' => $correlationId,
        ],
        'correlation_id' => $correlationId,
        'available_at' => now(),
    ],
);
```

- [ ] **Step 4: Verify payment and outbox behavior**

```bash
php artisan test tests/Feature/PaymentVerificationServiceTest.php tests/Feature/PaymentVerifyRaceConditionTest.php tests/Feature/FullVoucherFlowTest.php
```

Expected: one outbox event and one paid-state transition.

---

### Task 5: Make ambiguous voucher writes terminal for automation

**Files:**
- Create: `tests/Feature/OutboxDispatchPolicyTest.php`
- Modify: `app/Console/Commands/DispatchOutboxEvents.php`
- Modify: `app/Jobs/ProcessVoucherFulfillmentJob.php`
- Modify: `app/Services/Voucher/VoucherFulfillmentService.php`

**Interfaces:**
- Consumes: unprocessed, available outbox records.
- Produces: a dispatch lease for safe events; no selection of `needs_review`, ambiguous, or already-dispatched events.

- [ ] **Step 1: Write failing dispatch-policy tests**

Create rows representing pending, dispatched, processed, preflight-failed, and ambiguous events. Run `artisan('outbox:dispatch')` and assert only the pending safe row dispatches `ProcessVoucherFulfillmentJob`.

```php
Bus::fake();
$this->artisan('outbox:dispatch --limit=50')->assertSuccessful();
Bus::assertDispatchedTimes(ProcessVoucherFulfillmentJob::class, 1);
```

- [ ] **Step 2: Run and confirm current redispatch behavior fails the policy**

```bash
php artisan test tests/Feature/OutboxDispatchPolicyTest.php
```

Expected: failure because the current query includes `worker_result` values `failed` and `job_exception` and does not atomically lease rows.

- [ ] **Step 3: Restrict dispatch to never-dispatched safe rows**

Select only rows with `processed_at IS NULL`, `dispatched_at IS NULL`, due `available_at`, and event type `fulfill_voucher`. Inside a transaction with `lockForUpdate()->skipLocked()`, assign a `job_uuid` and `dispatched_at` before dispatching after commit. Do not clear the lease automatically after a job exception involving a possible remote write.

- [ ] **Step 4: Assert ambiguous processing is terminal**

Extend `VoucherFulfillmentAmbiguousTest`:

```php
$event->refresh();
$this->assertNotNull($event->processed_at);
$this->assertSame('needs_review', $event->worker_result);
$this->artisan('outbox:dispatch')->assertSuccessful();
Bus::assertNotDispatched(ProcessVoucherFulfillmentJob::class);
```

- [ ] **Step 5: Run voucher safety tests**

```bash
php artisan test tests/Feature/OutboxDispatchPolicyTest.php tests/Feature/VoucherFulfillmentAmbiguousTest.php tests/Feature/VoucherIdempotencyTest.php tests/Feature/FullVoucherFlowTest.php
```

Expected: all selected tests pass and the mock client receives at most one `addVoucher` call.

---

### Task 6: Package the branch pilot runtime

**Files:**
- Create: `Dockerfile`
- Create: `docker/entrypoint.sh`
- Create: `docker/nginx.conf`
- Create: `docker/supervisord.conf`
- Create: `compose.production.yaml`
- Create: `.dockerignore`
- Create: `docs/DEPLOYMENT.md`
- Create: `docs/BACKUP-RESTORE.md`
- Modify: `README.md`

**Interfaces:**
- Consumes: the same built image and environment for web, worker, and scheduler.
- Produces: web health endpoint, queue worker, scheduler, persistent private storage, and MySQL deployment instructions.

- [ ] **Step 1: Write runtime smoke assertions**

Add a shell smoke script that exits nonzero unless the image contains PHP extensions `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, and `pcntl`, compiled Vite assets, writable Laravel storage, and a successful `php artisan about`.

- [ ] **Step 2: Build the multi-stage image**

The Node stage runs `npm ci && npm run build`; the Composer stage runs `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`; the PHP runtime copies the application and built artifacts, enables required extensions, and runs as a non-root application user.

- [ ] **Step 3: Define separate processes**

Use one image with commands equivalent to:

```bash
php-fpm
php artisan queue:work database --tries=1 --timeout=90 --max-time=3600
php artisan schedule:work
```

Only the web process receives public traffic. All processes share the release version, private storage volume, and database configuration.

- [ ] **Step 4: Document safe deployment and restore**

`DEPLOYMENT.md` must contain exact required environment variables, first deploy, migration, worker/scheduler, health check, and rollback commands. `BACKUP-RESTORE.md` must cover MySQL dump, private-file snapshot, checksum verification, restoration into a new instance, and application-level sample verification.

- [ ] **Step 5: Verify build and runtime**

```bash
docker compose -f compose.production.yaml config
docker build -t stiflow:voucher-mvp .
docker run --rm stiflow:voucher-mvp docker/runtime-smoke.sh
docker compose -f compose.production.yaml up -d
curl --fail http://127.0.0.1:8080/up
docker compose -f compose.production.yaml exec app php artisan migrate:status
docker compose -f compose.production.yaml down
```

Expected: every command exits 0, the health endpoint returns HTTP 200, and the migration table shows all migrations applied.

---

### Task 7: Remove non-production behavior and run the release gate

**Files:**
- Modify: `database/seeders/*`
- Modify: `app/Integrations/Payments/*`
- Modify: `app/Services/Catalog/RecentPurchaseSocialProofService.php`
- Create: `docs/RELEASE-CHECKLIST.md`

**Interfaces:**
- Consumes: a configured branch pilot instance.
- Produces: a release candidate with simulations disabled and a recorded verification report.

- [ ] **Step 1: Write tests that reject simulated production behavior**

Assert the production environment cannot enable a gateway adapter that has no real provider transport, seed a known default password, or synthesize purchases when no order exists.

- [ ] **Step 2: Run tests and observe the current simulation failures**

```bash
php artisan test --filter='ProductionSafety|SocialProof|PaymentGateway'
```

Expected: failures identify simulated gateway responses, default credentials, or synthetic social proof.

- [ ] **Step 3: Disable unreleased modules explicitly**

Gateway adapters throw a typed `ProviderNotConfigured` exception until real credentials and transports are implemented. Development accounts are created only by a local-only seeder requiring explicit arguments. Empty purchase data returns an empty collection.

- [ ] **Step 4: Run the complete automated suite**

```bash
php artisan test
```

Expected: zero failures, zero errors. Placeholder tests do not count as feature evidence and must be listed as excluded later-phase coverage in the release checklist.

- [ ] **Step 5: Execute the manual pilot matrix**

Run voucher quantities 1, 5, and one allowed custom amount; one rejected proof; one wrong promoter code; one remote timeout simulation; and two concurrent dispatches. Record order ID, operation reference, before/after balance, local result, remote result, reconciliation result, and operator in `docs/RELEASE-CHECKLIST.md`.

## Self-review

- Spec coverage: Tasks 1–7 cover API transport, remote verification, migration order, payment/outbox uniqueness, ambiguous-result policy, deployment, backup, and removal of simulations for Voucher MVP.
- Deliberate exclusions: automatic gateway, full WordPress migration, CRM, affiliate, LMS, public-site integration, licensing, and multi-branch release tooling each require a separate implementation plan.
- Placeholder scan: implementation steps contain concrete files, commands, interfaces, and expected evidence.
- Type consistency: client and service signatures are identical between producer and consumer tasks.
