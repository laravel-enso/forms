<?php

namespace LaravelEnso\FormBuilder\app\Classes;

use LaravelEnso\FormBuilder\app\Classes\Validators\Fields;
use LaravelEnso\FormBuilder\app\Classes\Validators\Routes;
use LaravelEnso\FormBuilder\app\Classes\Validators\Actions;
use LaravelEnso\FormBuilder\app\Classes\Validators\Structure;

class Validator
{
    private $template;

    public function __construct($template)
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
