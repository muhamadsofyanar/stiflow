# Current State

**Recorded:** 6 September 2026  
**Architecture:** Next.js public site plus Laravel operational application  
**Release target:** Voucher MVP, manual bank transfer  
**Release classification:** implementation candidate; not approved for live transactions

## Completed in this iteration

- STIFIN client sends JSON, supports configured authentication header, keeps TLS verification enabled, and distinguishes unparseable responses.
- Admin verification checks the exact normalized promoter code against the configured branch through the STIFIN API. Remote errors and missing codes fail closed.
- Promoter verification records actor, timestamp, note, and audit log; normalized verified codes cannot be reused.
- Fresh migrations defer foreign keys that previously referenced tables created later.
- Payment approval validates amount and currency, locks records, and creates one outbox event with database-backed idempotency key per voucher order item.
- Outbox dispatch leases only never-dispatched, due voucher events. Ambiguous results become processed `needs_review` events and are not selected again.
- Midtrans, Xendit, and Finpay mock adapters throw `ProviderNotConfigured`; unsigned/unconfigured webhooks are rejected.
- Production demo seeding, synthetic social proof, and mock remote-license validation are disabled.
- Docker/Coolify files, worker, scheduler, health route, runtime smoke test, deployment guide, and backup/restore guide are present.

## Verification evidence

- Voucher MVP suite: **43 tests, 163 assertions, all passing** (see `docs/RELEASE-CHECKLIST.md` for the exact command).
- PHP syntax scan: all PHP files pass `php -l`.
- Vite production build: successful; `public/build/manifest.json` generated.
- Runtime smoke script: successful on the local PHP runtime.
- SQLite fresh migration: successful through migration `000046`.
- Full imported suite: **199 tests; 29 errors and 14 failures**. These are prototype defects in later CRM, affiliate, LMS, member, catalog, and licensing modules and keep the whole platform outside production status.
- Docker image/Compose and clean MySQL 8 migration were not executable in this workspace because Docker is unavailable.

## Remaining release blockers

1. Capture sanitized real STIFIN list, balance, success, 4xx, 5xx, timeout, and malformed-response fixtures; confirm the real authentication header/value format.
2. Build and run the image on a Docker host; execute clean MySQL 8 migration, rollback rehearsal, health check, worker, and scheduler checks.
3. Perform and record a restore drill on a new database and storage volume.
4. Run the controlled pilot matrix for voucher quantities 1, 5, and one allowed custom amount, plus rejection, wrong code, timeout, and concurrent dispatch cases.
5. Resolve all full-suite defects before enabling later modules. Seventy placeholder tests are not feature evidence.

## Next executable task

Use `docs/RELEASE-CHECKLIST.md` on a staging server with real branch configuration. Do not enter live credentials into source files or commit `.env`.

## Inputs needed for later phases

- Production WordPress SQL dump and media export for SEJOLI/LMS migration.
- Chosen payment gateway sandbox and webhook contract after the manual-transfer pilot passes.
- Private release/licensing service design before selling installations to other branches.
