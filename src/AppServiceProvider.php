<?php

namespace LaravelEnso\FormBuilder;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/config/forms.php', 'enso.forms');

        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], 'forms-config');

        $this->publishes([
            __DIR__.'/app/Forms' => app_path('Forms'),
        ], 'forms');

        $this->publishes([
            __DIR__.'/resources/js' => resource_path('js'),
        ], 'forms-assets');

        $this->publishes([
            __DIR__.'/resources/js' => resource_path('js'),
        ], 'enso-assets');
    }

    public function register()
    {
        //
    }
}
