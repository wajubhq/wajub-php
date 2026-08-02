# Wajub PHP SDK

Official server-side SDK for the [Wajub merchant API](https://docs.wajub.com). For embedded checkout in the browser, use [Wajub.js](https://docs.wajub.com/libraries/components/js). Use this SDK on your backend with a secret or restricted API key.

## Requirements

| | |
|---|---|
| **PHP** | 8.2 or later |
| **Extensions** | `json` |
| **Dependencies** | [Guzzle](https://github.com/guzzle/guzzle) 7.9+ (installed automatically) |

## Installation

```bash
composer require wajub/wajub-php
```

## Quick start

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

## Configuration

| Variable | Description |
|----------|-------------|
| `WAJUB_API_KEY` | API key (`pk_`, `sk_`, or `rk_`, including test variants) |
| `WAJUB_WEBHOOK_SECRET` | Webhook signing secret for `constructEvent()` |

Test mode is selected by your API key (`sk_test.…`), not by the URL.

## Resources

| Property | Methods |
|----------|---------|
| `$wajub->global` | `ping`, `channels`, `countries`, `currencies` |
| `$wajub->payments` | `create`, `initialize`, `retrieve`, `list`, `cancel`, `process`, `processSplit`, `listRefunds` |
| `$wajub->customers` | CRUD, `block`, `unblock`, `activate`, `deactivate`, tax IDs |
| `$wajub->refunds` | CRUD, list |
| `$wajub->transfers` | CRUD, list |
| `$wajub->beneficiaries` | CRUD, list |
| `$wajub->links` | CRUD, list |
| `$wajub->invoices` | CRUD, `send`, `markPaid`, `cancel` |
| `$wajub->accounts` | CRUD, `regenerateToken` |
| `$wajub->webhookEndpoints` | CRUD, `rotateSecret` |
| `$wajub->balance` | `retrieve` |
| `$wajub->events` | `list`, `retrieve`, `resend` |
| `$wajub->disputes` | `list`, `retrieve`, `submitEvidence`, `accept`, `close`, `sendMessage` |
| `$wajub->identity` | `resolve`, `validate` |
| `$wajub->tax` | Settings, rates, calculate, reports, codes, registrations |
| `$wajub->shield` | Settings, stats, blocklist |
| `$wajub->listen` | `config`, `auth` |
| `$wajub->webhooks` | `constructEvent` |

## Webhooks

```php
$event = $wajub->webhooks->constructEvent(
    $request->getContent(),
    $request->headers->get('X-Wajub-Signature'),
    $request->headers->get('X-Wajub-Timestamp'),
);
```

## Connect (Sync)

```php
use Wajub\RequestOptions;

$wajub->payments->create($params, new RequestOptions(sync: 'acct_sync_ref'));
```

## Documentation

Full reference, error handling, and pagination: [docs.wajub.com/libraries/sdks/php](https://docs.wajub.com/libraries/sdks/php)

## License

MIT
