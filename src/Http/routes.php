<?php

declare(strict_types=1);

/**
 * This file is part of the guanguans/filament-login-captcha.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

use Guanguans\FilamentLoginCaptcha\Http\Controllers\CaptchaController;
use Guanguans\FilamentLoginCaptcha\Http\Middleware\CleanObContents;
use Guanguans\FilamentLoginCaptcha\Http\Middleware\SetResponseContentType;
use Illuminate\Support\Facades\Route;

Route::get(config('route.uri'), CaptchaController::class)
    ->name(config('route.name'))
    ->middleware([
        SetResponseContentType::class,
        CleanObContents::class,
    ]);
