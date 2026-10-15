<p align="center"><a href="https://wajub.com" target="_blank"><img src="/art/logo.svg" width="280" alt="Wajub"></a></p>

<p align="center">
<a href="https://packagist.org/packages/wajub/wajub-php"><img src="https://img.shields.io/packagist/dt/wajub/wajub-php" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/wajub/wajub-php"><img src="https://img.shields.io/packagist/v/wajub/wajub-php" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/wajub/wajub-php"><img src="https://img.shields.io/packagist/l/wajub/wajub-php" alt="License"></a>
</p>

## Introduction

Official server-side SDK for the [Wajub API](https://docs.wajub.com): create payments, confirm them, pay out and verify webhooks from any PHP application, Laravel included.

It runs on your server with a secret (`sk.…`, `sk_test.…`) or restricted (`rk.…`, `rk_test.…`) API key. Never expose these keys in client-side code: in the browser, use [Wajub.js](https://docs.wajub.com/libraries/components/js) with your publishable key (`pk.…`, `pk_test.…`).

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.4 or later |
| Extension | `ext-json` |

## Installation

```bash
composer require wajub/wajub-php
```

## Quick start

Amounts are in the currency's **main unit**: `12.50` for GHS 12.50, `15000` for XAF 15,000.

```php
use Wajub\Wajub;

$wajub = new Wajub([
    'api_key' => getenv('WAJUB_API_KEY'),
    'webhook_secret' => getenv('WAJUB_WEBHOOK_SECRET'),
]);

$payment = $wajub->payments->create([
    'amount' => 15000,
    'currency' => 'XAF',
    'email' => 'buyer@example.com',
    'reference' => 'order-1042',
    'callback' => 'https://shop.example.com/order/complete',
]);

// Redirect the customer to $payment->authorization_url,
// or mount $payment->authorization_token with Wajub.js in your page.
```

Then confirm the result on your server: handle the `payment.succeeded` webhook, or call `$wajub->payments->retrieve($payment->id)`.

Parameters take the API's field names. What comes back is an object: read a field as a property (`$payment->status`) or as an array key (`$payment['status']`).

## Configuration

| Key | Description |
|-----|-------------|
| `api_key` | Secret or restricted API key (`sk.…`, `sk_test.…`, `rk.…`, `rk_test.…`). Required. |
| `webhook_secret` | Webhook signing secret (`whsec_…`), needed only for `webhooks->constructEvent()`. Falls back to `WAJUB_WEBHOOK_SECRET`. |
| `timeout` | Request timeout in seconds. |

The SDK does not read the API key from the environment: read `WAJUB_API_KEY` yourself and pass it as `api_key`, as above.

### Laravel

Add the keys to `config/services.php`, then bind the client once:

```php
// config/services.php
'wajub' => [
    'secret' => env('WAJUB_API_KEY'),
    'webhook_secret' => env('WAJUB_WEBHOOK_SECRET'),
],

// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->singleton(Wajub::class, fn () => new Wajub([
        'api_key' => config('services.wajub.secret'),
        'webhook_secret' => config('services.wajub.webhook_secret'),
    ]));
}
```

## Resources

| Purpose | Property | Methods |
|---------|----------|---------|
| Payments | `$wajub->payments` | `create`, `retrieve`, `list`, `cancel`, `process`, `processSplit`, `listRefunds` |
| Refunds | `$wajub->refunds` | `create`, `retrieve`, `list` |
| Customers | `$wajub->customers` | `create`, `retrieve`, `update`, `delete`, `list`, `block`, `unblock`, `activate`, `deactivate`, `listTaxIds`, `createTaxId`, `deleteTaxId` |
| Payment links | `$wajub->links` | `create`, `retrieve`, `update`, `delete`, `list` |
| Invoices | `$wajub->invoices` | `create`, `retrieve`, `update`, `delete`, `list`, `send`, `markPaid`, `cancel` |
| Payouts | `$wajub->transfers` | `create`, `retrieve`, `list` |
| Payout recipients | `$wajub->beneficiaries` | `create`, `retrieve`, `update`, `delete`, `list` |
| Destination checks | `$wajub->identity` | `resolve`, `validate` |
| Balance | `$wajub->balance` | `retrieve` |
| Disputes | `$wajub->disputes` | `list`, `retrieve`, `submitEvidence`, `accept`, `close`, `sendMessage` |
| Events | `$wajub->events` | `list`, `retrieve`, `resend` |
| Webhook endpoints | `$wajub->webhookEndpoints` | `create`, `retrieve`, `update`, `delete`, `list`, `rotateSecret` |
| Webhook verification | `$wajub->webhooks` | `constructEvent` |
| Connected accounts (Sync) | `$wajub->accounts` | `create`, `retrieve`, `update`, `delete`, `list`, `regenerateToken` |
| Tax | `$wajub->tax` | `getSettings`, `updateSettings`, `rates`, `calculate`, `reports`, `listCodes`, `retrieveCode`, `listRegistrations`, `createRegistration`, `retrieveRegistration`, `updateRegistration`, `deleteRegistration`, `jurisdictions`, `thresholds`, `thresholdAlerts` |
| Fraud (Shield) | `$wajub->shield` | `getSettings`, `updateSettings`, `stats`, `listBlocklist`, `addToBlocklist`, `removeFromBlocklist` |
| Reference data | `$wajub->global` | `ping`, `channels`, `countries`, `currencies` |

## Acting for a connected account

Pass the connected account with `sync` on the call:

```php
use Wajub\RequestOptions;

$wajub->payments->create($params, new RequestOptions(sync: 'acct_sync_ref'));
```

## Webhooks

Pass the **raw request body**, not parsed JSON, with the `X-Wajub-Signature` and `X-Wajub-Timestamp` headers. In a Laravel controller:

```php
use Illuminate\Http\Request;
use Wajub\Exception\WebhookSignatureVerificationError;
use Wajub\Wajub;

public function __invoke(Request $request, Wajub $wajub)
{
    try {
        $event = $wajub->webhooks->constructEvent(
            $request->getContent(),
            $request->header('X-Wajub-Signature'),
            $request->header('X-Wajub-Timestamp'),
        );
    } catch (WebhookSignatureVerificationError) {
        return response()->noContent(400);
    }

    if ($event['event'] === 'payment.succeeded') {
        // fulfil the order
    }

    return response()->noContent(200);
}
```

The event is an array; its type is in `event`. A delivery that does not verify throws `WebhookSignatureVerificationError`. If your server's clock drifts, pass a tolerance in seconds as a fourth argument. In Laravel, exempt the webhook route from CSRF verification.

## Pagination

`list()` returns a page you can walk one page at a time, or iterate across all pages; rows are arrays:

```php
$page = $wajub->payments->list(['per_page' => 50]);

foreach ($page->data as $payment) {
    echo $payment['id'].' '.$payment['status'];
}

if ($page->hasMore) {
    $next = $page->getNextPage();
}

foreach ($page->autoPagingIterator() as $payment) {
    // every row of every page
}
```

## Idempotency

Temporary failures are retried for you. To make a create safe to repeat on your side too, after a restart or in a queued job's retry, pass your own key:

```php
use Wajub\RequestOptions;

$wajub->payments->create($params, new RequestOptions(idempotencyKey: "order-{$orderId}"));
```

## Error handling

API errors throw a subclass of `Wajub\Exception\WajubError`:

```php
use Wajub\Exception\AuthenticationException;
use Wajub\Exception\InvalidRequestException;
use Wajub\Exception\WajubError;

try {
    $wajub->payments->create($params);
} catch (InvalidRequestException $e) {
    $fields = $e->errors; // field-level validation errors
} catch (AuthenticationException $e) {
    // check your API key
} catch (WajubError $e) {
    error_log("{$e->errorCode} {$e->httpStatus} {$e->getMessage()}");
}
```

| Class in `Wajub\Exception` | Raised on |
|----------------------------|-----------|
| `AuthenticationException` | `401` |
| `PermissionException` | `403` |
| `NotFoundException` | `404` |
| `InvalidRequestException` | `400`, `422` |
| `RateLimitException` | `429`, with `retryAfter` in seconds |
| `WajubError` | Any other error status, and the parent of the classes above |
| `ApiConnectionException` | No response: network error or timeout |

Every error carries `getMessage()`, `errorCode`, `httpStatus`, `errors` (the first message of each field) and `raw` (the response body). `getCode()` is always `0`: read `errorCode`.

## Testing

Use a sandbox key (`sk_test.…`): the key chooses the environment, and no real money moves. The test phone numbers and cards are on [Test scenarios](https://docs.wajub.com/developer-tools/testing/test-scenarios). To receive webhooks on your machine, forward them with the [Wajub CLI](https://docs.wajub.com/developer-tools/cli/listen).

## Documentation & support

- Documentation: [docs.wajub.com/libraries/sdks/php](https://docs.wajub.com/libraries/sdks/php)
- Report issues: [github.com/wajubhq/wajub-php/issues](https://github.com/wajubhq/wajub-php/issues)
- Report a security vulnerability: [security policy](https://github.com/wajubhq/wajub-php/security/policy)

## License

The Wajub PHP SDK is open-sourced software licensed under the [MIT license](LICENSE.md).
