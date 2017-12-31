<?php

namespace LaravelEnso\FormBuilder\app\Classes\Validators;

use LaravelEnso\FormBuilder\app\Exceptions\TemplateException;
use LaravelEnso\FormBuilder\app\Classes\Attributes\Fields as Attributes;

class Fields
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->checkFormat();

        collect($this->template->fields)->each(function ($field) {
            $this->checkAttributes($field);

            (new Meta($field))->validate();
        });
    }

    private function checkFormat()
    {
        if (!is_array($this->template->fields) || empty($this->template->fields)
            || collect($this->template->fields)->first(function ($field) {
                return !is_object($field);
            }) !== null
        ) {
            throw new TemplateException(__(
                'The fields attribute must be an array of objects with at least one element'
            ));
        }
    }

    private function checkAttributes($field)
    {
        $diff = collect(Attributes::List)
            ->diff(collect($field)->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(sprintf(
                'Mandatory Field Attribute(s) Missing: "%s"',
                $diff->implode('", "')
            )));
        }

        return $this;
    }
}
