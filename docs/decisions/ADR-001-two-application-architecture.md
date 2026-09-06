# ADR-001: Keep the Public Site Separate from STIFLow

**Status:** Accepted  
**Date:** 6 September 2026

## Decision

Keep `konsepstifin.com` as the existing Next.js public platform and replace `app.konsepstifin.com` with STIFLow Laravel. STIFLow becomes the system of record for accounts, commercial transactions, voucher fulfillment, CRM, affiliate, and LMS data as their phases are released.

## Reasons

- Preserves the existing public content and SEO surface.
- Avoids rewriting a working Next.js site before the transactional system is safe.
- Keeps public traffic isolated from payment and fulfillment workers.
- Allows a staged cutover from WordPress/SEJOLI.

## Consequences

- Two deployable applications remain.
- A versioned signed integration contract is required.
- Referral and UTM attribution must survive cross-domain navigation.
- Browser requests cannot be trusted to set price, ownership, promoter code, or payment status.
