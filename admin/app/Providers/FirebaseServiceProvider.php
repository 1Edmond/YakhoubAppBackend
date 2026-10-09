<?php

namespace App\Providers;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(Factory::class, function ($app) {
            $factory = new Factory();
            $firebaseConfig = getWebConfig('push_notification_key');
            if (!empty($firebaseConfig) && (is_file($firebaseConfig) || (is_string($firebaseConfig) && str_starts_with(trim($firebaseConfig), '{')))) {
                try {
                    $factory = $factory->withServiceAccount($firebaseConfig);
                } catch (\Throwable $e) {
                    // Fallback gracefully if service account is invalid
                }
            }
            return $factory;
        });

        $this->app->singleton(Auth::class, function ($app) {
            try {
                return $app->make(Factory::class)->createAuth();
            } catch (\Throwable $e) {
                return null;
            }
        });

        $this->app->singleton(Messaging::class, function ($app) {
            try {
                return $app->make(Factory::class)->createMessaging();
            } catch (\Throwable $e) {
                return null;
            }
        });

        // Optionally, you can bind it to a simpler alias
        $this->app->alias(Messaging::class, 'firebase.messaging');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
