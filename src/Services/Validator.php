<?php

namespace LaravelEnso\Forms\Services;

use LaravelEnso\Forms\Services\Validators\Actions;
use LaravelEnso\Forms\Services\Validators\Fields;
use LaravelEnso\Forms\Services\Validators\Routes;
use LaravelEnso\Forms\Services\Validators\Structure;
use LaravelEnso\Helpers\Services\Obj;

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
