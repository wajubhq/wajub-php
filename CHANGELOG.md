# Changelog

All notable changes to `wajub/wajub-php` will be documented in this file.

## v2.1.0 - 2026-10-15

### Deprecated

* `listen->config()` and `listen->auth()`, with `$wajub->listen`: the API answers them `410 Gone`. They still send the request, and now raise an `E_USER_DEPRECATED` notice. To receive webhooks on your machine, forward them with the Wajub CLI's [`wajub listen`](https://docs.wajub.com/developer-tools/cli/listen). They will be removed in the next major version

**Full Changelog**: https://github.com/wajubhq/wajub-php/compare/v2.0.1...v2.1.0

## v2.0.1 - 2026-10-11

### What's Changed

* Fix `Cannot instantiate abstract class Wajub\Objects\ApiObject` on `create()`, `retrieve()` and `update()` of `transfers`, `beneficiaries`, `links`, `invoices`, `accounts` and `webhookEndpoints`. In 2.0.0 the request was sent and the error was thrown on the answer, so the transfer, the beneficiary or the link existed and the caller never received it. If you retried a `transfers->create()` that failed this way without an idempotency key, check for a duplicate payout
* Return `Transfer`, `Beneficiary`, `Link`, `Invoice`, `Account` and `WebhookEndpoint` objects from those resources, read like `Payment`, `Customer` and `Refund` (`$transfer->status` or `$transfer['status']`)
* A create sent again with the same idempotency key while the first is still running now returns the first request's result instead of a `409` error
* `webhooks->constructEvent()` accepts a null signature or timestamp, as `$request->header()` returns when the header is missing, and throws `WebhookSignatureVerificationError` instead of a `TypeError`: your route answers 400, not 500

**Full Changelog**: https://github.com/wajubhq/wajub-php/compare/v2.0.0...v2.0.1

## v2.0.0 - 2026-09-15

### Breaking Changes

* Require PHP 8.4 by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Remove `update()` and `delete()` from `refunds` and `transfers`, the API has no such endpoints by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* `tax->reports()` now returns the report object instead of an empty list by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* `shield->addToBlocklist()` now returns the updated blocklist, same shape as `listBlocklist()` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1

### New Features

* `getNextPage()` and `autoPagingIterator()` walk every list to its last page by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Keep `action`, `confirm_url`, `simulator_url` and `crypto.deposit` on the payment returned by `process()` and `processSplit()` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Add `currency` filter to `balance->retrieve()` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Add `merchant_response` to `disputes->accept()` and `disputes->close()` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Describe every `Payment`, `Customer`, `Refund` and `Event` field, so your IDE completes them by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Ship Laravel Boost guidelines and the `wajub-php-development` skill by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1

### What's Changed

* An unreadable success answer throws `WajubError` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1

**Full Changelog**: https://github.com/wajubhq/wajub-php/compare/v1.1.1...v2.0.0
