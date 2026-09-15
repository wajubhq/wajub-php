# Wajub PHP SDK — agent guide

Server-side SDK for the Wajub merchant API. Framework-agnostic PHP 8.4+, Guzzle underneath.

## The one rule

The SDK mirrors the API, it never invents. Every method maps to a route in the merchant API and unwraps the exact key the API answers with. Before adding or changing a method:

1. Find the route and its controller in the API (`routes/api.php`, `app/Http/Controllers/**`).
2. Read the validation rules (request fields) and the `response()->json([...])` keys.
3. Mirror that: same path, same verb, same field names (`snake_case`, never renamed), `HttpUtils::pickResource($res, '<key>')` / `pickList($res, '<key>')` with the key the controller uses.
4. Public method names are `camelCase` and must match the resource table in the docs (`docs.wajub.com/libraries/sdks/php`).

If the API does not expose it, the SDK does not either. `refunds` and `transfers` have no `update`/`delete` for that reason.

## Layout

- `src/Wajub.php` — entry point, one property per resource.
- `src/Http/BaseClient.php` — headers, idempotency, retries, error mapping. All resources extend it.
- `src/Resources/Resource.php` — `create` / `retrieve` / `list`; `CrudResource` adds `update` / `delete`.
- `src/Resources/Pagination.php` — page-number and cursor pagination, both driven by `meta`.
- `src/Objects/*` — `ApiObject` subclasses with `@property-read` docblocks copied from the API resources.
- `src/Webhooks.php` — signature verification (`v1=` HMAC-SHA256 over `{timestamp}.{raw body}`).
- `resources/boost/` — Laravel Boost guidelines and skill shipped to consumers. Keep them in sync with the code.

## Commands

```bash
composer test      # Pest — every HTTP interaction is mocked with Guzzle's MockHandler, never call the real API
composer analyse   # PHPStan level 6
composer refactor  # Rector, PHP 8.4 sets — run before opening a PR
composer format    # Pint, PSR-12
```

A change to a resource needs a test asserting the request (method, path, query, JSON body) and the unwrapped response. See `tests/Feature/ResourcesTest.php`.

## Security invariants

- API keys travel only in the `Authorization` header; never log, echo or put them in exception messages.
- `constructEvent()` compares with `hash_equals`, enforces the timestamp tolerance and cannot be disabled.
- Idempotency keys come from `random_bytes`; every `POST`/`PUT` carries one so retries are safe.
- Retries: `429`, `500`, `502`, `503`, `504` and network errors only; `Retry-After` is honoured but capped at 10 s.
- Resource ids are always `rawurlencode`d into paths.
- A `2xx` with a non-JSON body throws `WajubError('invalid_response')` instead of returning an empty object.

## Do not

- Add an `X-Wajub-Version` header by default. The docs state no SDK sends it; users pass it through `RequestOptions(headers: [...])`.
- Change a documented method signature without a matching docs change.
- Put usage documentation in the README. It points to the docs site, like Laravel packages.
