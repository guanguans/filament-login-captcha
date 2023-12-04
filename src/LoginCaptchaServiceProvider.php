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

use Composer\InstalledVersions;
use Dcat\Admin\Admin;
use Dcat\Admin\Support\Helper;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LoginCaptchaServiceProvider extends PackageServiceProvider
{
    public static function trans(?string $key = null, array $replace = [], ?string $locale = null): null|array|string
    {
        return __("filament-login-captcha::$key", $replace, $locale);
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-login-captcha')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasRoute('api')
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('guanguans/filament-login-captcha');
            });
    }

    public function packageRegistered(): void
    {
        $this->registerPhraseBuilder()
            ->registerCaptchaBuilder();
    }

    public function packageBooted(): void
    {
        // $this->exceptRoutes = [
        //     'auth' => $uri = config('route.uri'),
        //     'permission' => $uri,
        // ];

        $this
            ->extendValidator()
            ->addSectionToAboutCommand();

        // $this->bootingCaptcha();
    }

    public function provides(): array
    {
        return [
            $this->toAlias(PhraseBuilder::class),
            $this->toAlias(CaptchaBuilder::class),
            PhraseBuilder::class,
            CaptchaBuilder::class,
        ];
    }

    protected function registerPhraseBuilder(): self
    {
        $this->app->singleton(PhraseBuilder::class, static fn (): PhraseBuilder => new PhraseBuilder(config('length'), config('charset')));
        $this->alias(PhraseBuilder::class);

        return $this;
    }

    protected function registerCaptchaBuilder(): self
    {
        $this->app->singleton(CaptchaBuilder::class, static function (Application $app): CaptchaBuilder {
            $captchaBuilder = new CaptchaBuilder(null, $app->get(PhraseBuilder::class));
            $captchaBuilder->build(
                config('width'),
                config('height'),
                config('font'),
                config('fingerprint')
            );

            return $captchaBuilder;
        });

        $this->alias(CaptchaBuilder::class);

        return $this;
    }

    protected function extendValidator(): self
    {
        Validator::extend(
            'filament_login_captcha',
            static fn ($attribute, $value): bool => login_captcha_check($value),
            self::trans('login-captcha.captcha_error')
        );

        return $this;
    }

    /**
     * @psalm-suppress InvalidCast
     * @psalm-suppress InvalidArgument
     */
    protected function bootingCaptcha(): self
    {
        Admin::booting(function (): void {
            $this->config = array_replace_recursive($this->config, config('admin.login_captcha', []));
            if (! config('enabled')) {
                return;
            }

            $loginPath = ltrim(admin_base_path('auth/login'), '/');
            if (Helper::matchRequestPath("GET:$loginPath")) {
                Admin::script((string) view(sprintf('%s::captcha', $this->getName())));
            }

            if (Helper::matchRequestPath("POST:$loginPath")) {
                $validator = Validator::make(Request::post(), [
                    'captcha' => 'required|filament_login_captcha',
                ]);

                if ($validator->fails()) {
                    throw new HttpResponseException($this->validationErrorsResponse($validator));
                }
            }
        });

        return $this;
    }

    /**
     * @param class-string $class
     */
    protected function alias(string $class): self
    {
        $this->app->alias($class, $this->toAlias($class));

        return $this;
    }

    /**
     * @param class-string $class
     */
    protected function toAlias(string $class): string
    {
        return str($class)
            ->replaceFirst(__NAMESPACE__, '')
            ->start('\\FilamentLoginCaptcha')
            ->replaceFirst('\\', '')
            ->explode('\\')
            ->map(static fn (string $name): string => Str::snake($name, '-'))
            ->implode('.');
    }

    /**
     * @throws \JsonException
     */
    protected function addSectionToAboutCommand(): self
    {
        if (! class_exists(InstalledVersions::class)) {
            return $this;
        }

        AboutCommand::add('Filament Login Captcha', function (): array {
            $fullPackageName = "guanguans/{$this->package->name}";

            $installedVersions = collect(InstalledVersions::getAllRawData())
                ->pluck('versions')
                ->first(static fn (array $installedVersions): bool => isset($installedVersions[$fullPackageName]), []);

            $composerJson = json_decode(
                file_get_contents(base_path("vendor/{$fullPackageName}/composer.json")),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            return collect(($installedVersions[$fullPackageName] ?? []) + $composerJson)
                ->except([
                    'install_path',
                    'readme',
                    'reference',
                ])
                ->filter(static fn ($value): bool => \is_string($value) && $value)
                ->mapWithKeys(static fn ($value, $key) => [Str::headline($key) => $value])
                ->toArray();
        });

        return $this;
    }
}
