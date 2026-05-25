<?php

namespace App\Providers;

use App\Models\Player;
use App\Observers\PlayerObserver;
use App\Models\ForecastMatch;
use App\Observers\ForecastMatchObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Login;
use App\Listeners\TrackUserLogin;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Wire the SOOP API client with config-driven credentials so the rest
        // of the app can resolve it from the container without knowing details.
        $this->app->singleton(\App\Services\Soop\SoopApiClient::class, function () {
            return new \App\Services\Soop\SoopApiClient(
                baseUrl:        (string) config('services.soop.base_url'),
                clientId:       (string) config('services.soop.client_id'),
                userAgent:      (string) config('services.soop.user_agent'),
                timeoutSeconds: (int)    config('services.soop.timeout'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, TrackUserLogin::class);
        Player::observe(PlayerObserver::class);
        ForecastMatch::observe(ForecastMatchObserver::class);
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(6)
            : null,
        );
    }
}
