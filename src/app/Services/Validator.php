<?php

namespace LaravelEnso\Forms\app\Services;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Services\Validators\Fields;
use LaravelEnso\Forms\app\Services\Validators\Routes;
use LaravelEnso\Forms\app\Services\Validators\Actions;
use LaravelEnso\Forms\app\Services\Validators\Structure;

class Validator
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function run()
    {
        (new Structure($this->template))->validate();
        (new Actions($this->template))->validate();
        (new Routes($this->template))->validate();
        (new Fields($this->template))->validate();
    }
}
