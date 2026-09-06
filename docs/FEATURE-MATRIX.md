# STIFLow Feature Matrix

| Capability | Source/reference | Present in prototype | Production status | Target phase |
|---|---|---:|---|---:|
| Promoter login and role | STIFLow | Yes | Partial | 1 |
| Remote promoter validation | voucher plugin | No | Blocker | 1 |
| Voucher order and price snapshot | STIFLow | Yes | Requires hardening | 1 |
| Manual transfer proof | STIFLow | Yes | Requires authorization/storage tests | 1 |
| Admin payment approval | STIFLow | Yes | Requires DB uniqueness and concurrency test | 1 |
| Transactional outbox | STIFLow | Yes | Requires dispatch lease and retry policy | 1 |
| STIFIN JSON/auth adapter | voucher plugin | Partial | Blocker | 1 |
| Ambiguous-result reconciliation | STIFLow | Yes | Requires operator acceptance test | 1 |
| Audit trail | STIFLow | Yes | Requires retention and coverage review | 1 |
| Coolify/Docker deployment | architecture | No | Blocker | 1 |
| Backup and tested restore | architecture | No | Blocker | 1 |
| Payment gateway | prototype mocks | Simulated only | Disabled | 2 |
| Product catalog and checkout | SEJOLI/STIFLow | Yes | Admin product/variant CRUD verified; checkout pilot still required | 3 |
| Member entitlement | SEJOLI/STIFLow | Partial | Placeholder test coverage | 3 |
| WordPress production migration | WordPress/SEJOLI | No | Awaiting dump/export | 3 |
| CRM and lead ownership | public site/STIFLow | Partial | Admin contacts and pipeline baseline verified | 4 |
| Referral tree and commissions | SEJOLI/STIFLow | Partial | Placeholder test coverage | 4 |
| LMS, quiz, progress | DW SEJOLI LMS/STIFLow | Partial | Admin course baseline verified; lesson/quiz pilot pending | 5 |
| Signed public-site integration | Next.js | No | Contract not implemented | 6 |
| White-label installer/update | STIFLow mocks | Simulated only | Not usable | 7 |

`Present` means code or schema exists, not that the behavior is verified. A feature is production-ready only after its acceptance gate passes.
