<?php

declare(strict_types=1);

use Wajub\Version;

/**
 * The version this client tells the API it is.
 *
 * v2.0.0 was tagged and published to Packagist with this constant still
 * reading '1.1.1'. It travels in the User-Agent of every request, so a
 * merchant on 2.0.0 appeared in the logs as one on 1.1.1 — and support had no
 * way to tell what anyone was actually running.
 *
 * The changelog is written by the release process, so it is the one place
 * that always knows the version being shipped. If these two disagree, the
 * release is wrong, whichever one of them is.
 */
function changelogVersion(): string
{
    $changelog = (string) file_get_contents(dirname(__DIR__, 2).'/CHANGELOG.md');

    preg_match('/^##\s+v?(\d+\.\d+\.\d+)/m', $changelog, $matches);

    return $matches[1] ?? '';
}

it('announces the version the changelog says was released', function () {
    expect(Version::VERSION)->toBe(changelogVersion());
});

it('sends that version to the API', function () {
    // The constant is not decoration: it is how a request identifies itself.
    $client = new ReflectionClass(\Wajub\Http\BaseClient::class);

    expect(file_get_contents($client->getFileName()))->toContain("'wajub-php/'.Version::VERSION");
});
