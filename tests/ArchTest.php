<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->security();

arch('no debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('source files use strict types')
    ->expect('Wajub')
    ->toUseStrictTypes();
