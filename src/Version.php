<?php

declare(strict_types=1);

namespace Wajub;

final class Version
{
    /**
     * What this package is, as it says so to the API.
     *
     * v2.0.0 was tagged and published to Packagist carrying '1.1.1' here: the
     * client announced itself as a version it was not, and support could not
     * tell from a request what a merchant was actually running. A release
     * that does not update this line is not a release — see RELEASING.md.
     */
    public const string VERSION = '2.0.1';

    public const string API_URL = 'https://api.wajub.com';
}
