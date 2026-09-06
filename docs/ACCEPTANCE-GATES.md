# Acceptance Gates

## Gate V1 — STIFIN read contract

- Client sends the configured auth header and `Accept: application/json`.
- Promoter and balance responses normalize from captured contract fixtures.
- Transport, HTTP, and parse failures remain distinguishable.

## Gate V2 — Promoter identity

- Admin cannot verify a promoter unless the exact normalized STIFIN code appears in the configured branch list.
- Remote failure does not change local verification state.
- A verified code belongs to only one active local promoter account.
- Checkout middleware rejects unverified promoters.

## Gate V3 — Payment approval

- Only authorized staff can download proof and approve or reject it.
- Approved amount and currency equal the order attempt.
- Repeated or concurrent approval produces one durable outbox event.
- Approval, rejection, and proof replacement are audited.

## Gate V4 — Voucher fulfillment

- One stable request reference is used per order item.
- POST body is JSON and contains only server-derived values.
- Success creates one operation record and completes the order.
- A second worker execution does not issue a second POST.
- Timeout, transport loss, 5xx, or unparseable response creates one open reconciliation case and never retries the POST automatically.
- Voucher products generate zero affiliate commission.

## Gate V5 — Database and runtime

- Fresh MySQL 8 migration succeeds.
- Full rollback succeeds on an empty test database.
- Web, queue worker, and scheduler run as separate processes from one image.
- Health endpoint fails when required production configuration is absent.
- Backup restoration is tested on a new database and private-file volume.

## Gate V6 — Pilot

- Controlled transactions cover quantities 1, 5, and one custom allowed amount.
- Local order totals match bank evidence and STIFIN credited units.
- No duplicate voucher addition occurs during concurrent dispatch tests.
- Every ambiguous case is reconciled with an operator note and audit record.
- No mock gateway, default password, synthetic purchase, or external placeholder image is active.

Production use starts only after V1–V6 have recorded evidence. Later feature modules are excluded from this release even if their routes or schemas already exist.
