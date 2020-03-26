<?php

namespace LaravelEnso\Forms\App\Services;

use LaravelEnso\Forms\App\Services\Validators\Actions;
use LaravelEnso\Forms\App\Services\Validators\Fields;
use LaravelEnso\Forms\App\Services\Validators\Routes;
use LaravelEnso\Forms\App\Services\Validators\Structure;
use LaravelEnso\Helpers\App\Classes\Obj;

class Validator
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function run(): void
    {
        (new Structure($this->template))->validate();
        (new Actions($this->template))->validate();
        (new Routes($this->template))->validate();
        (new Fields($this->template))->validate();
    }
}
