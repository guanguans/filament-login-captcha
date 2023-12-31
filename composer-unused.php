<?php

declare(strict_types=1);

/**
 * This file is part of the guanguans/filament-login-captcha.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;
use ComposerUnused\ComposerUnused\Configuration\PatternFilter;
use Webmozart\Glob\Glob;

return static fn (Configuration $config): Configuration => $config
    ->addNamedFilter(NamedFilter::fromString('symfony/config'))
    ->addPatternFilter(PatternFilter::fromString('/symfony\/.*/'))
    ->setAdditionalFilesFor('icanhazstring/composer-unused', [
        __FILE__,
        ...Glob::glob(__DIR__.'/config/*.php'),
    ]);
