---
name: wajub-php-development
description: "Use when integrating the Wajub PHP SDK (wajub/wajub-php) in a PHP or Laravel application. Trigger whenever the query mentions Wajub by name, the Wajub\\Wajub client, mobile money or card payments in Africa through Wajub, or tasks such as creating a payment or checkout, redirecting to an authorization_url, processing a Mobile Money push, refunding, paying out with transfers and beneficiaries, listing or paginating resources, handling Wajub webhooks and signature verification, catching WajubError exceptions, or mocking the SDK in Pest or PHPUnit tests. Do not trigger for Stripe, Paystack, Flutterwave or other payment providers, or for the Wajub.js browser component."
license: MIT
metadata:
  author: wajub
---
# Wajub PHP SDK

## Documentation

The reference lives at https://docs.wajub.com/libraries/sdks/php. When available, use the `search-docs` tool before guessing a field name; payload fields are the API's exact `snake_case` names and are never translated.

## Client setup

<!-- Plain PHP -->
```php
use Wajub\Wajub;

$wajub = new Wajub([
    'api_key' => getenv('WAJUB_API_KEY'),           // sk. or sk_test. key, required
    'webhook_secret' => getenv('WAJUB_WEBHOOK_SECRET'), // whsec_, only for webhooks
]);
```

<!-- Laravel: config/services.php -->
```php
'wajub' => [
    'secret' => env('WAJUB_API_KEY'),
    'webhook_secret' => env('WAJUB_WEBHOOK_SECRET'),
],
```

<!-- Laravel: AppServiceProvider::register() -->
```php
$this->app->singleton(Wajub::class, fn () => new Wajub([
    'api_key' => config('services.wajub.secret'),
    'webhook_secret' => config('services.wajub.webhook_secret'),
]));
```

Other options: `idempotency_key_prefix` (default `wajub`), `http_client` (your own `GuzzleHttp\Client`), `max_network_retries` (default `2`), `timeout` (seconds, default `30`).

## Creating a payment

<!-- Redirect checkout -->
```php
$payment = $wajub->payments->create([
    'amount' => 25000,            // major unit: 25 000 XAF
    'currency' => 'XAF',
    'email' => 'amina@example.com',
    'reference' => 'order-4172',
    'callback' => route('checkout.complete'),
]);

return redirect()->away($payment->authorization_url);
```

<!-- Embedded checkout with Wajub.js: omit callback, hand the token to the browser -->
```php
$payment = $wajub->payments->create(['amount' => 25000, 'currency' => 'XAF']);
$token = $payment->authorization_token;
```

<!-- Mobile Money push, no hosted page -->
```php
$payment = $wajub->payments->create(['amount' => 25000, 'currency' => 'XAF', 'phone' => '+237670000000']);

$wajub->payments->process($payment->id, ['channel' => 'cm.mtn', 'phone' => '+237670000000']);
```

A channel is `country.operator` (`cm.mtn`, `cm.orange`). The payer approves on their handset; the outcome arrives on the webhook, so never treat `process()` returning as "paid".

## Reading results

- `create()` and `retrieve()` return an `ApiObject`: `$payment->id`, `$payment->status`, `$payment->authorization_url`. Unknown fields are still readable, nothing is dropped.
- `list()` returns a `PagedResult`; `$page->data` rows are plain arrays (`$row['id']`), `$page->meta` holds `total`, `per_page`, `current_page`, `last_page`. Pass `['cursor' => ...]` to switch to cursor pagination (`meta.next_cursor`, `meta.has_more`); `getNextPage()` follows either mode.
- `process()` returns the transaction plus, when the payer has a next step, `action`, `confirm_url` (redirect channels) or `simulator_url` (sandbox).
- `refunds` and `transfers` have no `update()`/`delete()`: the API does not allow it.

<!-- Pagination -->
```php
$page = $wajub->payments->list(['status' => 'success', 'per_page' => 50]);

if ($page->hasMore) {
    $page = $page->getNextPage();
}

foreach ($wajub->payments->list()->autoPagingIterator() as $row) {
    // every page, lazily
}
```

## Idempotency and connected accounts

<!-- Natural idempotency key and X-Sync header -->
```php
use Wajub\RequestOptions;

$wajub->payments->create($params, new RequestOptions(
    idempotencyKey: "order-{$order->id}",
    sync: $seller->wajub_account_id, // optional, acts for a connected account
));
```

Every `POST`/`PUT` already carries a generated `Idempotency-Key`; retries on `429`/`5xx`/network errors reuse it. Prefer a queued job over a synchronous call inside a web request, and give the job the same key.

## Refunds and payouts

<!-- Refund -->
```php
$refund = $wajub->refunds->create([
    'payment' => $payment->id,            // required: the payment id
    'reason' => 'requested_by_customer',  // required: duplicate, fraudulent, requested_by_customer, service_not_delivered, product_not_received, wrong_amount, network_error, transaction_error
    'amount' => 5000,                     // optional: partial refund, major unit
]);
$wajub->payments->listRefunds($payment->id);
```

<!-- Transfer to a phone number -->
```php
$transfer = $wajub->transfers->create([
    'amount' => 100000,
    'currency' => 'XAF',
    'beneficiary' => ['name' => 'Amina Diallo', 'channel' => 'cm.mtn', 'phone' => '+237670000000'],
    'reference' => 'payout-892',
], new RequestOptions(idempotencyKey: 'payout-892'));
```

## Webhooks

<!-- Laravel controller -->
```php
use Wajub\Exception\WebhookSignatureVerificationError;

try {
    $event = $wajub->webhooks->constructEvent(
        $request->getContent(),                 // raw body, never $request->all()
        $request->header('X-Wajub-Signature'),
        $request->header('X-Wajub-Timestamp'),
    );
} catch (WebhookSignatureVerificationError) {
    return response()->noContent(400);
}

if ($event['event'] === 'payment.succeeded') {   // name is in `event`, not `type`
    HandleWajubEvent::dispatch($event);
}

return response()->noContent(200);
```

Exempt the route from CSRF (`$middleware->validateCsrfTokens(except: ['webhooks/wajub'])`), answer `200` fast and do the work in a queued job. Tolerance is 300 s; pass a fourth argument to widen it.

## Errors

<!-- Catch the specific one first -->
```php
use Wajub\Exception\InvalidRequestException;
use Wajub\Exception\RateLimitException;
use Wajub\Exception\WajubError;

try {
    $wajub->payments->create($params);
} catch (InvalidRequestException $e) {
    return response()->json(['fields' => $e->errors], 422);
} catch (RateLimitException $e) {
    return response()->noContent(503)->header('Retry-After', (string) ($e->retryAfter ?? 5));
} catch (WajubError $e) {
    Log::error('wajub failed', ['code' => $e->errorCode, 'status' => $e->httpStatus]);
    throw $e;
}
```

| Class | Raised on |
| --- | --- |
| `AuthenticationException` | 401 |
| `PermissionException` | 403 |
| `NotFoundException` | 404 |
| `InvalidRequestException` | 400, 422 |
| `RateLimitException` | 429, `retryAfter` in seconds |
| `ApiConnectionException` | network failure or timeout |
| `WajubError` | everything else, parent of all of the above |

`$e->getCode()` is always `0`; the real code is `$e->errorCode`.

## Testing

<!-- Offline SDK with a Guzzle mock -->
```php
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

$mock = new MockHandler([
    new Response(201, [], json_encode([
        'authorization_url' => 'https://pay.wajub.com/tok_test',
        'authorization_token' => 'tok_test',
        'transaction' => ['id' => 'trx_test', 'status' => 'pending', 'amount' => 25000],
    ])),
]);

$wajub = new Wajub([
    'api_key' => 'sk_test.fake',
    'http_client' => new Client(['handler' => HandlerStack::create($mock)]),
]);
```

In Laravel, swap the singleton: `$this->app->instance(Wajub::class, $wajub)`.

## Verification

1. Payment created with the exact `snake_case` field names from the docs, amount in the major unit.
2. Redirect uses `authorization_url`; embedded checkout uses `authorization_token`.
3. Webhook route reads `getContent()`, is CSRF-exempt, and returns `200` before doing work.
4. Order of `catch` blocks: specific exceptions before `WajubError`.
5. Tests use a `MockHandler`; no real key, no network.

## Common pitfalls

- Passing amounts in minor units (cents) — Wajub takes the major unit.
- Reading `$event['type']` instead of `$event['event']`.
- Verifying the webhook with `$request->all()` or `$request->json()`, which re-encodes the body and breaks the HMAC.
- Calling `env('WAJUB_API_KEY')` outside `config/`, which returns `null` once config is cached.
- Treating `process()` as final; the payer still has to approve on their phone.
