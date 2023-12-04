<?php

declare(strict_types=1);

/**
 * This file is part of the guanguans/filament-login-captcha.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Guanguans\FilamentLoginCaptcha\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CleanObContents
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next): Response
    {
        ob_get_contents() && ob_clean();

        return $next($request);
    }
}
