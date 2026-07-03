<?php

namespace App\Providers;

use App\Http\Controllers\PublicStorageController;
use App\Support\PublicWebRoot;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config([
            'filesystems.disks.property_media.root' => PublicWebRoot::storagePath(),
        ]);

        $this->app->booted(function (): void {
            Route::get('storage/{path}', [PublicStorageController::class, 'show'])
                ->where('path', '.*')
                ->name('public-storage.show');
        });
    }
}
