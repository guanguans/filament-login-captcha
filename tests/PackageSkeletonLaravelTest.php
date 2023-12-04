<?php

declare(strict_types=1);

/**
 * This file is part of the guanguans/filament-login-captcha.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Guanguans\FilamentLoginCaptchaTests;

use Guanguans\FilamentLoginCaptcha\PackageSkeleton;

/**
 * @internal
 *
 * @coversNothing
 *
 * @small
 */
final class PackageSkeletonLaravelTest extends LaravelTestCase
{
    #[RequiresPhpExtension('pgsql')]
    public function testTest(): void
    {
        $this->markTestSkipped('The PostgreSQL extension is not available');
        $this->assertTrue(PackageSkeleton::test());
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
