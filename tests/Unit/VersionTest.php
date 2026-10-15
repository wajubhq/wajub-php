<?php

declare(strict_types=1);

use Wajub\Version;

/**
 * The version this client tells the API it is, in the User-Agent of every
 * request, is the latest version in the changelog: if the two disagree, the
 * release is wrong.
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
