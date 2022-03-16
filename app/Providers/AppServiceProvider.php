<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        if(Auth::check()){
            // Pass User profile details to layouts view
            view()->composer('layouts.*', function ($view) {
                $view->with([
                    'user'  =>  \App\Models\User::where('id', auth()->id())->first()
                ]);
            });
        }

    }
}
