# STIFLow Branch Platform

STIFLow is a standalone Laravel platform for a STIFIN branch. It is intended to replace the branch's WordPress, SEJOLI, and supporting plugins at `app.konsepstifin.com` while keeping the public Next.js site at `konsepstifin.com`.

## Current status

The supplied application is a prototype and is not approved for live payments or voucher transactions. The first release target is a Voucher MVP using manual bank transfer, admin verification, a transactional outbox, one STIFIN write attempt, and human reconciliation for uncertain results.

## Required reading

- [`docs/MASTER-CONTEXT.md`](docs/MASTER-CONTEXT.md): product decisions and transaction invariants.
- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md): two-application topology and module boundaries.
- [`docs/FEATURE-MATRIX.md`](docs/FEATURE-MATRIX.md): source coverage and phase ownership.
- [`docs/API-CONTRACT-STIFIN.md`](docs/API-CONTRACT-STIFIN.md): observed API contract and evidence gaps.
- [`docs/DATA-DICTIONARY.md`](docs/DATA-DICTIONARY.md): Voucher MVP entities and retention classes.
- [`docs/MIGRATION-MAP.md`](docs/MIGRATION-MAP.md): WordPress/SEJOLI/LMS mapping and required exports.
- [`docs/ACCEPTANCE-GATES.md`](docs/ACCEPTANCE-GATES.md): production gates.
- [`docs/CURRENT-STATE.md`](docs/CURRENT-STATE.md): last audited state and next task.
- [`docs/superpowers/plans/2026-09-06-voucher-mvp-hardening.md`](docs/superpowers/plans/2026-09-06-voucher-mvp-hardening.md): executable implementation plan.

## Technology baseline

- PHP 8.2 or newer
- Laravel 12
- Livewire 3
- MySQL 8
- Database queue with a separate worker
- Laravel scheduler as a separate process
- Docker/Coolify deployment target

## Release rule

Code presence is not release evidence. A feature is usable only after the corresponding gate in `docs/ACCEPTANCE-GATES.md` passes with recorded test or pilot evidence.

## Deployment

- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md): Docker Compose/Coolify installation, update, and rollback.
- [`docs/BACKUP-RESTORE.md`](docs/BACKUP-RESTORE.md): verified MySQL and private-storage recovery procedure.
