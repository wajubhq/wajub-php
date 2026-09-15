# Contributing

Thank you for considering contributing to the Wajub PHP SDK.

## Before you start

- Behaviour documented on [docs.wajub.com](https://docs.wajub.com/libraries/sdks/php) is the contract. A change that alters a documented method, field or exception needs a documentation change to go with it.
- Method names follow PHP conventions (`camelCase`); payload fields keep the exact `snake_case` names the API uses. See the [SDK naming conventions](https://docs.wajub.com/libraries/sdks/conventions).
- Please open an issue before starting a large change so we can agree on the approach.

## Pull requests

1. Fork the repository and create a branch from `main`.
2. Install dependencies with `composer install`.
3. Add or update tests in `tests/` for your change. The suite uses [Pest](https://pestphp.com).
4. Make sure everything passes locally:

   ```bash
   composer test      # Pest
   composer analyse   # PHPStan
   composer refactor  # Rector (PHP 8.4 sets, dead code, type declarations)
   composer format    # Pint, PSR-12
   ```

   The SDK mirrors the merchant API: a new method must map to a route in the API and unwrap the exact key the API answers with. Do not add endpoints or fields that the API does not expose.

5. Open the pull request against `main` with a clear description of what changed and why. The changelog is generated from the GitHub release notes, do not edit `CHANGELOG.md` by hand.

## Reporting a security issue

Please do not open a public issue. Follow the [security policy](SECURITY.md) instead.
