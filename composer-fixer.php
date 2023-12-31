#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * This file is part of the guanguans/filament-login-captcha.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Process\Process;

require __DIR__.'/vendor/autoload.php';

collect(
    Finder::create()
        ->in([
            __DIR__.'/',
            // __DIR__.'/vendor-bin/*/',
        ])
        ->exclude('vendor')
        ->name('composer.json')
        ->depth(0)
        ->files()
)
    ->each(static function (SplFileInfo $splFileInfo): void {
        /** @noinspection PhpUnhandledExceptionInspection */
        collect(json_decode($splFileInfo->getContents(), true, 512, JSON_THROW_ON_ERROR))
            ->only([
                // 'require',
                'require-dev',
            ])
            ->each(static function ($packagist, $env) use ($splFileInfo): void {
                $symfonyStyle = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
                $symfonyStyle->note(sprintf('The composer file(%s) %s updating...', $splFileInfo->getRealPath(), $env));

                $hydratedPackagist = collect($packagist)
                    ->filter(static fn ($version, $package): bool => ! in_array(
                        $package,
                        ['php', 'laravel/facade-documenter'],
                        true
                    ))
                    ->map(static fn ($version, $package): string => (string) $package)
                    ->implode(' ');
                if (empty($hydratedPackagist)) {
                    $symfonyStyle->note(sprintf('The composer file(%s) %s nothing to update.', $splFileInfo->getRealPath(), $env));
                    $symfonyStyle->newLine();

                    return;
                }

                /** @noinspection ToStringSimplificationInspection */
                $command = str("COMPOSER_MEMORY_LIMIT=-1 composer require $hydratedPackagist -W --ansi -v")
                    ->when('require-dev' === $env, static fn (Illuminate\Support\Stringable $command) => $command->append(' --dev'))
                    ->__toString();
                $symfonyStyle->note($command);
                Process::fromShellCommandline($command, $splFileInfo->getPath(), ['COMPOSER_MEMORY_LIMIT' => -1])
                    ->setTimeout(null)
                    ->mustRun(static function ($type, $buffer) use ($symfonyStyle): void {
                        $symfonyStyle->write($buffer);
                    });

                $symfonyStyle->note(sprintf('The composer file(%s) %s updated.', $splFileInfo->getRealPath(), $env));
                $symfonyStyle->newLine();
            });
    });
