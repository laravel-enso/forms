<?php

namespace LaravelEnso\FormBuilder;

use Illuminate\Support\ServiceProvider;

class FormBuilderServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/app/Forms' => app_path('Forms'),
        ], 'forms-template');
    }

    public function register()
    {
        //
    }
}
