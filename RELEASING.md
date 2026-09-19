# Releasing wajub/wajub-php

Packagist serves this repository directly. A release is a tag, and everything
else follows from it — which is why the one manual step below is the one that
has already gone wrong.

## The step that matters

`src/Version.php` carries the version this client sends to the API in its
`User-Agent`. **v2.0.0 was published with that constant still reading
`1.1.1`**: every merchant on 2.0.0 appeared in the logs as one on 1.1.1, and
support had no way to tell what anyone was running.

`tests/Unit/VersionTest.php` now fails when the constant and the changelog
disagree, so this cannot ship silently again. It is a guard, not a substitute
for doing it:

1. Update `src/Version.php` to the version being released.
2. Let `update-changelog.yml` write the changelog entry, or write it by hand
   with the same `## vX.Y.Z` heading — that heading is what the guard reads.
3. Tag `vX.Y.Z` and push the tag. Packagist picks it up from the webhook.

## What a major version owes

Removing a public method is a breaking change, and merchants call these
methods from code we do not see. `PaymentsResource::initialize()` was removed
locally at one point while the published 2.0.0 still carried it — that
divergence is how a client breaks for someone who upgraded in good faith.

If a method goes, say so in the changelog under **Breaking Changes**, and give
it a deprecation in a minor release first where that is possible.

## Before tagging

```bash
composer update --prefer-dist --no-interaction   # the lock file is not committed current
vendor/bin/pest --ci
vendor/bin/phpstan analyse
```

Note: `composer.lock` in this repository is out of step with `composer.json`
(Pest is declared but absent from the lock), so `composer install` alone gives
a tree without the test runner. CI runs `composer update`, which is why it
passes there and not on a fresh clone.
