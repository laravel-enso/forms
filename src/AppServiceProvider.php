<?php

namespace LaravelEnso\FormBuilder;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'forms-config');

        $this->publishes([
            __DIR__.'/app/Forms' => app_path('Forms'),
        ], 'forms');

        $this->publishes([
            __DIR__.'/resources/assets/js' => resource_path('assets/js'),
        ], 'forms-assets');

        $this->publishes([
            __DIR__.'/resources/assets/js' => resource_path('assets/js'),
        ], 'enso-assets');

        $this->mergeConfigFrom(__DIR__.'/config/forms.php', 'enso.forms');
    }

    public function register()
    {
        //
    }
}
