<?php

namespace LaravelEnso\Forms;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/config/forms.php', 'enso.forms');

        $this->publishes([
            __DIR__.'/config' => config_path('enso'),
        ], ['forms-config', 'enso-config']);

        (new Collection(['Forms/Builders/ModelForm', 'Forms/Templates/template']))
            ->each(fn ($stub) => $this->publishes([
                __DIR__."/../stubs/{$stub}.stub" => app_path("{$stub}.php"),
            ], 'forms-resources'));
    }
}
