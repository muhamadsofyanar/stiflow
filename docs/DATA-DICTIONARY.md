# Voucher MVP Data Dictionary

| Entity | Purpose | Critical fields/invariants |
|---|---|---|
| `users` | Local identity | role and active status govern access |
| `promoter_profiles` | Local promoter binding | `stifin_code` unique; verified only after remote branch membership match |
| `branch_settings` | Single-branch configuration | exactly one active `branch_code`; currency `IDR` in MVP |
| `products` | Sellable definitions | voucher product is active, login-only, and commission-ineligible |
| `voucher_product_configs` | Voucher quantity rules | unit price, minimum, maximum, presets are server-controlled |
| `orders` | Commercial transaction | immutable number, user, currency, totals, promoter-code snapshot |
| `order_items` | Purchased units | quantity and unit price snapshot; voucher target equals order/user promoter code |
| `payment_attempts` | One payment attempt | amount and currency equal order; verified timestamp only on approval |
| `payment_proofs` | Private transfer evidence | one current proof per attempt; file private; reviewer and review timestamp recorded |
| `outbox_events` | Transaction-to-worker handoff | one `fulfill_voucher` event per order item; stable correlation ID |
| `fulfillments` | Local fulfillment state | one fulfillment per order item/type; success is terminal |
| `voucher_fulfillments` | Voucher-specific snapshot | before/after balances, units, target code, remote reference |
| `stifin_operations` | Append-only external-call evidence | unique operation type/request reference; redacted request; raw response and outcome |
| `reconciliation_cases` | Human resolution queue | created for every ambiguous write; resolution is audited |
| `audit_logs` | Actor/action history | append-only for financial and access-sensitive changes |

## Retention classification

| Class | Examples | MVP handling |
|---|---|---|
| Financial record | orders, payment attempts, approval audit | retain; never hard-delete through UI |
| External-operation evidence | STIFIN operation and reconciliation | retain with request secrets redacted |
| Private user file | payment proof | private disk, authorized download only |
| Credential | API token/secret | encrypted configuration or environment only; never logs/database snapshots |
| Public content | product names and descriptions | publishable through read-only catalog contract |

## Snapshot rule

Transactional records retain the values used at the moment of purchase. Later changes to promoter profile, product price, branch details, or referral relationships never rewrite completed order snapshots.
