# Wajub PHP SDK

[![Packagist Version](https://img.shields.io/packagist/v/wajub/wajub-php)](https://packagist.org/packages/wajub/wajub-php)
[![PHP Version](https://img.shields.io/packagist/php-v/wajub/wajub-php)](https://packagist.org/packages/wajub/wajub-php)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Official **server-side** SDK for the [Wajub merchant API](https://docs.wajub.com). Accept mobile-money and card payments across Africa with a Stripe-inspired, resource-oriented client.

Use **[Wajub.js](https://docs.wajub.com/libraries/components/js)** for embedded checkout in the browser. Use this SDK on your backend with a secret (`sk_`) or restricted (`rk_`) API key — never expose secret keys in client-side code.

## Features

- Resource-oriented API (`$wajub->payments`, `$wajub->customers`, …)
- Automatic `Idempotency-Key` on mutating requests (override per call)
- Typed exceptions per HTTP status (`AuthenticationException`, `RateLimitException`, …)
- Automatic retries on 429 and 5xx (max 2, exponential backoff)
- Page-based pagination with `autoPagingIterator()` and `getNextPage()`
- Webhook signature verification (HMAC-SHA256, timestamp tolerance)

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.2 or later |
| Extension | `json` |
| HTTP client | [Guzzle](https://github.com/guzzle/guzzle) 7.9+ (installed automatically) |

## Installation

```bash
composer require wajub/wajub-php
```

## Quick start

Amounts are passed in the **smallest currency unit** (e.g. cents for EUR/USD; whole francs for XAF).

### Redirect checkout

```php
<?php

use Wajub\Wajub;

$wajub = new Wajub([
    'api_key' => getenv('WAJUB_API_KEY'),
]);

$payment = $wajub->payments->create([
    'amount' => 15000,
    'currency' => 'XAF',
    'email' => 'buyer@example.com',
    'callback' => 'https://shop.example.com/order/complete',
]);

header('Location: ' . $payment->authorization_url);
```

### Inline / overlay (embed token)

Omit `callback` when the buyer stays on your page and you mount Wajub.js:

```php
$embed = $wajub->payments->create([
    'amount' => 15000,
    'currency' => 'XAF',
    'metadata' => ['mode' => 'embed'],
]);

// Pass to Wajub.js: $embed->authorization_token
```

`create()` and `retrieve()` return a typed `Payment` object — prefer property access (`$payment->authorization_url`). List pages from `list()` yield associative arrays.

## Laravel

Register the client in a service provider or bind it in the container:

```php
// config/services.php
'wajub' => [
    'secret' => env('WAJUB_API_KEY'),
    'webhook_secret' => env('WAJUB_WEBHOOK_SECRET'),
],
```

```php
use Wajub\Wajub;

$wajub = new Wajub([
    'api_key' => config('services.wajub.secret'),
    'webhook_secret' => config('services.wajub.webhook_secret'),
]);

$payment = $wajub->payments->create([/* … */]);
return redirect($payment->authorization_url);
```

### Webhook route

Use the **raw request body** — not `$request->all()`:

```php
use Illuminate\Http\Request;
use Wajub\Exception\WebhookSignatureVerificationError;

Route::post('/webhooks/wajub', function (Request $request) {
    global $wajub; // or resolve from container

    try {
        $event = $wajub->webhooks->constructEvent(
            $request->getContent(),
            $request->header('X-Wajub-Signature'),
            $request->header('X-Wajub-Timestamp'),
        );
    } catch (WebhookSignatureVerificationError $e) {
        abort(400);
    }

    match ($event['type'] ?? null) {
        'payment.succeeded' => /* fulfill order */,
        default => null,
    };

    return response('', 200);
});
```

## Configuration

| Variable | Description |
|----------|-------------|
| `WAJUB_API_KEY` | Secret or restricted API key (`sk_`, `sk_test.`, `rk_`, …) |
| `WAJUB_WEBHOOK_SECRET` | Webhook signing secret (`whsec_`) for `constructEvent()` |

Test mode is selected by your API key prefix (`sk_test.…`), not by the API URL. Production calls always go to `https://api.wajub.com`.

Constructor options:

```php
$wajub = new Wajub([
    'api_key' => getenv('WAJUB_API_KEY'),
    'webhook_secret' => getenv('WAJUB_WEBHOOK_SECRET'),
    'idempotency_key_prefix' => 'myshop',
    'max_network_retries' => 2,
    'timeout' => 30.0,
    'http_client' => $customGuzzleClient, // optional
]);
```

## Resources (merchant API)

| Property | Methods |
|----------|---------|
| `$wajub->global` | `ping`, `channels`, `countries`, `currencies` |
| `$wajub->payments` | `create`, `initialize`, `retrieve`, `list`, `cancel`, `process`, `processSplit`, `listRefunds` |
| `$wajub->customers` | `create`, `retrieve`, `update`, `delete`, `list`, `block`, `unblock`, `activate`, `deactivate`, `listTaxIds`, `createTaxId`, `deleteTaxId` |
| `$wajub->refunds` | `create`, `retrieve`, `list` |
| `$wajub->transfers` | `create`, `retrieve`, `list` |
| `$wajub->beneficiaries` | `create`, `retrieve`, `update`, `delete`, `list` |
| `$wajub->links` | `create`, `retrieve`, `update`, `delete`, `list` |
| `$wajub->invoices` | `create`, `retrieve`, `update`, `delete`, `list`, `send`, `markPaid`, `cancel` |
| `$wajub->accounts` | `create`, `retrieve`, `update`, `delete`, `list`, `regenerateToken` |
| `$wajub->webhookEndpoints` | `create`, `retrieve`, `update`, `delete`, `list`, `rotateSecret` |
| `$wajub->balance` | `retrieve` |
| `$wajub->events` | `list`, `retrieve`, `resend` |
| `$wajub->disputes` | `list`, `retrieve`, `submitEvidence`, `accept`, `close`, `sendMessage` |
| `$wajub->identity` | `resolve`, `validate` |
| `$wajub->tax` | `getSettings`, `updateSettings`, `rates`, `calculate`, `reports`, `listCodes`, `retrieveCode`, `listRegistrations`, `createRegistration`, `retrieveRegistration`, `updateRegistration`, `deleteRegistration`, `jurisdictions`, `thresholds`, `thresholdAlerts` |
| `$wajub->shield` | `getSettings`, `updateSettings`, `stats`, `listBlocklist`, `addToBlocklist`, `removeFromBlocklist` |
| `$wajub->listen` | `config`, `auth` |
| `$wajub->webhooks` | `constructEvent` (local — no HTTP) |

## Sync (Connect)

Pass a connected account reference on any mutating call:

```php
use Wajub\RequestOptions;

$wajub->payments->create($params, new RequestOptions(sync: 'acct_sync_ref'));
```

## Webhooks

```php
use Wajub\Exception\WebhookSignatureVerificationError;

try {
    $event = $wajub->webhooks->constructEvent(
        $rawBody, // string — must be raw body bytes, not parsed JSON
        $_SERVER['HTTP_X_WAJUB_SIGNATURE'] ?? '',
        $_SERVER['HTTP_X_WAJUB_TIMESTAMP'] ?? '',
    );
} catch (WebhookSignatureVerificationError $e) {
    http_response_code(400);
    exit;
}

if (($event['type'] ?? '') === 'payment.succeeded') {
    // fulfill order
}
```

During local development, use the [Wajub CLI](https://github.com/wajub/wajub-cli) to forward webhooks to your machine.

## Pagination

List methods return a `PagedResult` whose rows are associative arrays. Typed resources such as `Payment` use property access (`->`) on `create()` and `retrieve()`:

```php
$page = $wajub->payments->list(['per_page' => 50]);

foreach ($page->autoPagingIterator() as $row) {
    echo $row['id'], ' ', $row['status'], PHP_EOL;
}
}

// Manual page control
$first = $wajub->payments->list();
if ($first->hasMore) {
    $second = $first->getNextPage();
}
```

## Idempotency

POST and PUT requests automatically receive an `Idempotency-Key` header. Pass your own to safely retry a specific operation:

```php
use Wajub\RequestOptions;

$wajub->payments->create($params, new RequestOptions(idempotencyKey: "order-{$orderId}"));
```

## Error handling

Catch typed exceptions to branch on HTTP status:

```php
use Wajub\Exception\AuthenticationException;
use Wajub\Exception\InvalidRequestException;
use Wajub\Exception\RateLimitException;

try {
    $wajub->payments->create($params);
} catch (InvalidRequestException $e) {
    // 400 / 422 — validation errors in $e->errors
} catch (AuthenticationException $e) {
    // 401 — bad API key
} catch (RateLimitException $e) {
    // 429 — back off and retry
}
```

## Development

Run the test suite from this directory:

```bash
composer test
```

## Documentation & support

- Full API reference: [docs.wajub.com/libraries/sdks/php](https://docs.wajub.com/libraries/sdks/php)
- Report issues: [github.com/wajub/wajub-php/issues](https://github.com/wajub/wajub-php/issues)

## License

MIT — see [LICENSE](LICENSE).
