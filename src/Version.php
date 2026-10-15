<?php

declare(strict_types=1);

namespace Wajub;

final class Version
{
    /**
     * What this package is, as it says so to the API in its User-Agent.
     * It matches the latest version in CHANGELOG.md.
     */
    public const string VERSION = '2.0.1';

    public const string API_URL = 'https://api.wajub.com';
}
