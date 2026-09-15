# Changelog

All notable changes to `wajub/wajub-php` will be documented in this file.

## v2.0.0 - 2026-09-15

### Breaking Changes

* Require PHP 8.4 by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Remove `update()` and `delete()` from `refunds` and `transfers`, the API has no such endpoints by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* `tax->reports()` now returns the report object instead of an empty list by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* `shield->addToBlocklist()` now returns the updated blocklist, same shape as `listBlocklist()` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* `listen->config()` and `listen->auth()` now unwrap the `realtime` and `data` keys instead of returning the raw envelope by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1

### New Features

* Follow cursor pagination (`meta.next_cursor`, `meta.has_more`) in `getNextPage()` and `autoPagingIterator()` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Keep `action`, `confirm_url`, `simulator_url` and `crypto.deposit` on the payment returned by `process()` and `processSplit()` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Add `currency` filter to `balance->retrieve()` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Add `merchant_response` to `disputes->accept()` and `disputes->close()` by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Document every `Payment`, `Customer`, `Refund` and `Event` field from the API resources by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Ship Laravel Boost guidelines and the `wajub-php-development` skill by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1

### What's Changed

* Cap `Retry-After` at 10 seconds by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Throw `WajubError` on a `2xx` response with a non-JSON body by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Migrate the test suite to Pest 5 by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Add PHPStan, Rector, Pint (PSR-12) and GitHub Actions workflows by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1
* Move usage documentation from the README to docs.wajub.com by @mckenziearts in https://github.com/wajubhq/wajub-php/pull/1

**Full Changelog**: https://github.com/wajubhq/wajub-php/compare/v1.1.1...v2.0.0
