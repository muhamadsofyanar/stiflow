# STIFLow Master Context

**Status:** Architecture A approved 6 September 2026  
**Product:** STIFLow Branch Platform  
**First production target:** automated voucher fulfillment after an admin verifies a manual bank transfer

## Product boundary

STIFLow replaces WordPress, SEJOLI, and branch-specific plugins at `app.konsepstifin.com`. The public site at `konsepstifin.com` remains a Next.js application for SEO, content, promoter discovery, campaigns, and lead acquisition.

Each branch receives an isolated installation with its own domain, MySQL database, private storage, branding, bank account, provider credentials, and STIFIN API credentials. There is one maintained codebase; branch-specific forks are prohibited.

## System of record

| Data | System of record |
|---|---|
| Public articles, SEO pages, city pages | `konsepstifin.com` |
| Users, orders, payments, voucher fulfillment | STIFLow |
| Promoter registration and voucher balance | STIFIN central API |
| Leads, referral attribution, CRM activity | STIFLow after Phase 4 |
| Courses, access, progress, certificates | STIFLow after Phase 5 |
| Branch configuration and integration secrets | The branch's STIFLow instance |

## Locked decisions

- Laravel 12 modular monolith with Blade, Livewire, MySQL 8, database queue, and scheduler.
- Manual transfer is the first payment method. Gateway automation follows only after the manual path is stable.
- Only a remotely verified promoter can buy vouchers.
- Vouchers can only be credited to the logged-in promoter's own STIFIN code.
- A successful payment creates an outbox event; the worker performs the STIFIN write.
- A timeout or uncertain response from the STIFIN write is never retried automatically. It becomes `needs_review`.
- Voucher purchases never create affiliate commission.
- `konsepstifin.com` and STIFLow exchange signed server-to-server requests; browser-supplied ownership or price data is never trusted.
- Data from production WordPress is migrated only after a database dump and media export are supplied and reconciled.

## Delivery phases

1. Voucher MVP hardening and branch pilot.
2. One real payment gateway with fail-closed webhook verification.
3. Product sales, checkout, membership, and WordPress/SEJOLI migration.
4. CRM and affiliate ledger.
5. LMS and digital delivery migration.
6. Full Next.js integration.
7. White-label packaging, licensing, updates, backup, and rollback.

## Non-negotiable transaction invariants

1. Money is never accepted as verified without an auditable verification event.
2. One approved payment produces at most one voucher outbox event.
3. One order item uses one stable STIFIN request reference.
4. A known successful STIFIN operation is never posted again.
5. An ambiguous write creates a reconciliation case and no automatic retry.
6. Price, quantity, branch code, promoter code, and currency are server-side snapshots.
7. Every state transition involving payment or vouchers records actor, subject, timestamp, and before/after state.

## Source inputs

- `stiflow-branch-source.zip`: working Laravel prototype.
- `sejoli-stifin-voucher.zip`: behavioral reference for STIFIN endpoints and JSON payload.
- `sejoli.zip`: behavioral reference for commerce, member, referral, and affiliate concepts.
- `dw-sejoli-lms.zip`: behavioral reference for LMS entities and access.
- `konsepstifin-platform-national-v0.5.1-final.zip`: current public Next.js platform.
- `2026-09-06-stifin-branch-platform-architecture-design (1).md`: approved architecture source.

## Known facts and unknowns

Known from source:

- Observed STIFIN endpoints are documented in `API-CONTRACT-STIFIN.md`.
- The legacy plugin sends voucher writes as JSON and supports a configurable authentication header.
- Current Laravel code sends the voucher write as form data and ignores configured API credentials.
- Current source has 70 placeholder tests using only `assertTrue(true)`.
- PHP, Composer dependencies, Docker packaging, and a MySQL runtime are absent from the supplied archive/environment.

Unknown until integration testing:

- Exact production authentication header name and value format.
- Formal success/error response schemas and whether HTTP 2xx can contain a business failure.
- Rate limits and maintenance behavior.
- Whether `Dispos` is enforced as an idempotency key by the central API.
- Production WordPress table prefixes, plugin versions, row counts, media inventory, and custom fields.

## Definition of the first usable release

The release is a branch-only pilot. A verified promoter creates a voucher order, uploads proof, an authorized administrator approves it, one outbox event is dispatched, and the worker either records confirmed success or opens a reconciliation case. It includes no real payment gateway, no simulated provider, and no claim that later CRM/LMS/affiliate modules are production-ready.
