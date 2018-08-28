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
        collect($this->template->sections)
            ->each(function ($section) {
                $this->checkFormat($section);

                collect($section->fields)->each(function ($field) {
                    $this->checkAttributes($field);
                    $this->checkValue($field);
                    (new Meta($field))->validate();
                });
            });
    }

    private function checkFormat($section)
    {
        if (!is_array($section->fields) || empty($section->fields)
            || collect($section->fields)->first(function ($field) {
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
            throw new TemplateException(__(
                'Mandatory Field Attribute(s) Missing: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkValue($field)
    {
        if ($field->meta->type === 'input' && $field->meta->content === 'checkbox') {
            if (!is_bool($field->value)) {
                throw new TemplateException(__(
                    'Chexboxes must have a boolean default value: ":field"',
                    ['field' => $field->name]
                ));
            }

            return;
        }

        if ($field->meta->type === 'select' && property_exists($field->meta, 'multiple') && $field->meta->multiple) {
            if (!is_array($field->value) && !is_object($field->value)) {
                throw new TemplateException(__(
                    'Multiple selects must have an array default value: ":field"',
                    ['field' => $field->name]
                ));
            }

            return;
        }
    }
}
