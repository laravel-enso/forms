<?php

namespace LaravelEnso\FormBuilder;

use Illuminate\Support\ServiceProvider;

class FormBuilderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/resources/assets/js/components' => resource_path('assets/js/vendor/laravel-enso/components'),
            __DIR__.'/resources/assets/js/classes' => resource_path('assets/js/vendor/laravel-enso/classes'),
        ], 'forms-component');

        $this->publishes([
            __DIR__.'/resources/assets/js/components' => resource_path('assets/js/vendor/laravel-enso/components'),
            __DIR__.'/resources/assets/js/classes' => resource_path('assets/js/vendor/laravel-enso/classes'),
        ], 'enso-update');
    }

    public function register()
    {
        //
    }
}
