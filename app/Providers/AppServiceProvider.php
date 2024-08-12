<?php

namespace App\Providers;

use App\Services\CaptchaService;
use App\Services\EmailOtpService;
use App\Request\UserRegistrationRequest;
use App\Services\UserRegistrationService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider to register application services.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * This method binds services into the service container as singletons,
     * ensuring that the same instance is used throughout the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(CaptchaService::class, function () {
            return new CaptchaService();
        });

        $this->app->singleton(EmailOtpService::class, function () {
            return new EmailOtpService();
        });

        $this->app->singleton(UserRegistrationRequest::class, function () {
            return new UserRegistrationRequest();
        });

        // Register UserRegistrationService with dependencies
        $this->app->singleton(UserRegistrationService::class, function ($app) {
            return new UserRegistrationService(
                $app->make(CaptchaService::class),
                $app->make(EmailOtpService::class),
                $app->make(UserRegistrationRequest::class)
            );
        });
    }

    /**
     * Bootstrap the application services.
     *
     * This method is called after all services are registered.
     * It can be used to perform tasks that should be done at the start
     * of the application, such as registering event listeners or
     * publishing configuration files.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
