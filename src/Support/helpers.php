<?php

declare(strict_types=1);

/**
 * This file is part of the guanguans/filament-login-captcha.
 *
 * (c) guanguans <ityaozm@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

use Guanguans\FilamentLoginCaptcha\Facades\CaptchaBuilder;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

if (! function_exists('login_captcha_check')) {
    /**
     * 登录验证码检查.
     */
    function login_captcha_check(string $value): bool
    {
        return Guanguans\FilamentLoginCaptcha\PhraseBuilder::comparePhrases(
            Session::pull(config('captcha_phrase_session_key')),
            $value
        );
    }
}

if (! function_exists('login_captcha_url')) {
    /**
     * 获取登录验证码 url 地址.
     */
    function login_captcha_url(?string $routeName = null): string
    {
        return admin_route(
            $routeName ?? config('route.name'),
            ['random' => Str::random()]
        );
    }
}

if (! function_exists('login_captcha_content')) {
    /**
     * 获取登录验证码图像内容.
     *
     * @psalm-suppress InvalidNullableReturnType
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidArgument
     *
     * @noinspection PhpVoidFunctionResultUsedInspection
     */
    function login_captcha_content(int $quality = 90): string
    {
        Session::put(config('captcha_phrase_session_key'), CaptchaBuilder::getPhrase());

        return CaptchaBuilder::get($quality);
    }
}

if (! function_exists('str')) {
    /**
     * @codeCoverageIgnore
     */
    function str(mixed $string = null): Illuminate\Support\Stringable|Stringable
    {
        if (0 === func_num_args()) {
            return new class() implements \Stringable {
                public function __call($method, $parameters)
                {
                    return Str::$method(...$parameters);
                }

                public function __toString(): string
                {
                    return '';
                }
            };
        }

        return Str::of($string);
    }
}
