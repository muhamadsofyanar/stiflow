# Observed STIFIN API Contract

**Evidence level:** reverse-engineered from `sejoli-stifin-voucher` version 0.10.1. Production behavior must be proven against the authorized endpoint before live voucher writes.

## Configuration

| Variable | Meaning |
|---|---|
| `STIFIN_API_BASE_URL` | Base URL, observed default `https://apro.stifin.id/api` |
| `STIFIN_AUTH_HEADER` | Header name supplied by STIFIN, commonly `Authorization` |
| `STIFIN_AUTH_VALUE` | Complete header value, for example a bearer value |
| `STIFIN_USER_ID` | Operator/system identifier used in voucher payload |
| `STIFIN_CONNECT_TIMEOUT` | Connection timeout in seconds |
| `STIFIN_TIMEOUT` | Total request timeout in seconds |

The adapter sends `Accept: application/json`. Voucher writes send `Content-Type: application/json`. TLS verification remains enabled. Authentication configuration is optional for local contract tests but a production readiness check must reject live mode when the required header/value is absent.

## Endpoints

### List promoters for a branch

`GET /proGetCab/pro/{branchCode}`

Observed promoter fields include `KodeID` and `Nama`. The legacy plugin accepts a top-level list or a list wrapped by `data`, `Data`, or `result`. Only normalized code and display name need to be retained for verification; password-like or unrelated personal fields returned by the API are discarded.

### Read promoter voucher balance

`GET /voucherGet/getTotVoucher/{promoterCode}`

Observed paid/free fields are `SaldoJ` and `SaldoF`. The adapter may accept documented aliases in responses but emits one internal result:

```json
{"paid": 10, "free": 2}
```

### Add vouchers

`POST /voucherPos/editCabVoucher/{branchCode}`

```json
{
  "KodeID": "KHU-ABU-02",
  "Jumlah": "1",
  "JmlFree": "0",
  "SaldoJ": "10",
  "SaldoF": "2",
  "Dispos": "DISPOS-ORDER-INV-20260906-0001-42",
  "Ket": "Pembelian voucher via STIFLow cabang KHU - Order INV-20260906-0001",
  "UserID": "STIFLOW-KHU"
}
```

The observed plugin serializes numeric fields as strings. STIFLow must preserve this until an integration test proves numeric JSON values are accepted.

## Result classification

| Condition | Local outcome | Automatic POST retry |
|---|---|---:|
| HTTP 2xx and parseable accepted response | `success` | No |
| HTTP 4xx | `fail_4xx` | No |
| HTTP 5xx | `needs_review` | No |
| connection/total timeout during POST | `ambiguous_timeout` | No |
| transport loss during POST | `ambiguous_transport` | No |
| unparseable 2xx body | `unparseable` | No |
| local validation/configuration error before send | `preflight_error` | Allowed only after operator correction and a new controlled action |

HTTP 2xx is not sufficient for production until the business-success schema is confirmed. The pilot must record sanitized raw responses for evidence.

## Contract test evidence required before production

- One real list-promoters response with sensitive fields removed.
- One real balance response before and after a controlled voucher addition.
- One controlled successful voucher response.
- One rejected request response.
- Authentication failure behavior.
- Confirmation whether repeated `Dispos` is rejected, ignored, or applied twice.
