# Migration Map

## Production WordPress export required

No production database contents were included in the supplied archives. Migration execution therefore remains blocked until the following are exported:

- SQL dump with table prefix and database version.
- WordPress uploads/media archive with checksums.
- Active plugin list and exact versions.
- SEJOLI products, orders, payments, coupons, users, memberships, referrals, commissions, and payouts.
- DW SEJOLI LMS courses, lessons, quizzes, enrollments, progress, and certificates.
- Custom user/order metadata keys and row counts.

## Target mapping

| Legacy concept | STIFLow target | Reconciliation key |
|---|---|---|
| WordPress user | `users` | normalized email plus legacy user ID map |
| SEJOLI product | `products` and related config | legacy product ID |
| SEJOLI order | `orders`, `order_items`, `payment_attempts` | legacy order ID and invoice number |
| Coupon | `coupons` | normalized coupon code |
| Membership/access | entitlement/enrollment records | user + product/course + source ID |
| Affiliate sponsor | `promoter_profiles.sponsor_promoter_id` | STIFIN code plus legacy affiliate ID |
| Commission | append-only commission ledger | source order + beneficiary + rule snapshot |
| Payout | payout and payout entries | legacy payout ID |
| Course/module/lesson | LMS tables | legacy content ID |
| Progress/quiz attempt | LMS progress tables | user + lesson/quiz + legacy row ID |
| Media | `digital_assets` or public media | SHA-256 plus legacy attachment ID |

## Migration sequence

1. Snapshot source database and media; record hashes and row counts.
2. Import into staging tables without mutating production source.
3. Normalize identities and create persistent legacy-to-new ID maps.
4. Import products and content before transactional dependents.
5. Import users and promoter bindings without granting remote verification automatically.
6. Import orders, payments, entitlements, affiliate ledger, and LMS progress.
7. Compare counts, sums, orphan records, duplicate emails/codes, and access samples.
8. Run a dry cutover, measure downtime, and produce a discrepancy report.
9. Freeze writes, rerun incremental import, reconcile, and switch checkout.
10. Preserve the source snapshot read-only for rollback and audit.

## Prototype migration defects to repair in Phase 1

- `orders.coupon_id` references `coupons` before the table exists.
- `courses.cover_image_id` and `lessons.digital_asset_id` reference `digital_assets` before the table exists.
- communication tables reference `integration_connections` before the table exists.

The fix creates the columns first and adds deferred foreign keys in a later migration after all referenced tables exist. A clean MySQL 8 migration and rollback are mandatory evidence.
