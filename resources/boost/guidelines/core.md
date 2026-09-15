# Wajub PHP SDK

- `wajub/wajub-php` is the server-side SDK for the Wajub merchant API (mobile money and card payments across Africa). It holds the secret key, creates payments, reads their status and verifies webhooks. Never use it in browser-facing code.
- IMPORTANT: activate the `wajub-php-development` skill when creating payments, transfers or refunds, handling Wajub webhooks, or writing tests that touch the SDK.
- Full reference: https://docs.wajub.com/libraries/sdks/php
- Entry point is `new \Wajub\Wajub(['api_key' => ..., 'webhook_secret' => ...])`. In Laravel, bind it once as a singleton in `AppServiceProvider::register()` and read keys from `config('services.wajub.*')`, never from `env()` outside a config file.
- Resources are properties of the client: `$wajub->payments`, `$wajub->customers`, `$wajub->refunds`, `$wajub->transfers`, `$wajub->beneficiaries`, `$wajub->links`, `$wajub->invoices`, `$wajub->disputes`, `$wajub->events`, `$wajub->webhookEndpoints`, `$wajub->accounts`, `$wajub->balance`, `$wajub->identity`, `$wajub->tax`, `$wajub->shield`, `$wajub->global`, `$wajub->webhooks`.
- Method names are `camelCase`; payload and response fields keep the API's exact `snake_case` names (`authorization_url`, `per_page`, `decline_code`). Never rename a field.
- Amounts are in the currency's major unit: `25000` with `XAF` is 25 000 francs, `12.50` with `GHS` is a decimal.
- `create()` / `retrieve()` return an `ApiObject` with property access (`$payment->authorization_url`). `list()` returns a `PagedResult` whose `data` rows are plain arrays (`$row['id']`).
- Every `POST`/`PUT` gets a generated `Idempotency-Key`. Pass a natural one (an order id) with `new \Wajub\RequestOptions(idempotencyKey: ...)`; use `RequestOptions(sync: $accountId)` to act on a connected account.
- Errors extend `\Wajub\Exception\WajubError`; read `$e->errorCode`, `$e->httpStatus`, `$e->errors`, never `$e->getCode()` (always `0`). Catch `InvalidRequestException` and `RateLimitException` before the base class.
- Webhooks: `$wajub->webhooks->constructEvent($request->getContent(), $request->header('X-Wajub-Signature'), $request->header('X-Wajub-Timestamp'))`. Always pass the raw body, never `$request->all()`. The event name lives in `$event['event']`.
- Tests: pass a Guzzle client built on a `MockHandler` through the `http_client` option; never hit the real API from a test.
