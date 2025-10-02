<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Firestore;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Contract\Storage;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('firebase.factory', function ($app) {
            return (new Factory)
                ->withServiceAccount(config('services.firebase.credentials'))
                ->withDatabaseUri(config('services.firebase.database_url'));
        });

        $this->app->singleton(Firestore::class, function ($app) {
            return $app->make('firebase.factory')->createFirestore();
        });

        $this->app->singleton(Database::class, function ($app) {
            return $app->make('firebase.factory')->createDatabase();
        });

        $this->app->singleton(Auth::class, function ($app) {
            return $app->make('firebase.factory')->createAuth();
        });

        $this->app->singleton(Storage::class, function ($app) {
            return $app->make('firebase.factory')
                ->withDefaultStorageBucket(config('services.firebase.storage_bucket'))
                ->createStorage();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}