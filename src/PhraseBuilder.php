<?php

declare(strict_types=1);

/**
 * This file is part of the guanguans/filament-login-captcha.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Guanguans\FilamentLoginCaptcha;

use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\Traits\Tappable;

class PhraseBuilder extends \Gregwar\Captcha\PhraseBuilder
{
    // use Conditionable;
    use Macroable;
    use Tappable;
}
