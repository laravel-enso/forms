<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Attributes\Fields as Attributes;
use LaravelEnso\Forms\app\Exceptions\TemplateValueException;
use LaravelEnso\Forms\app\Exceptions\TemplateFormatException;
use LaravelEnso\Forms\app\Exceptions\TemplateAttributeException;

class Fields
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->template->get('sections')
            ->each(function ($section) {
                $this->checkFormat($section);

                $section->get('fields')->each(function ($field) {
                    $this->checkAttributes($field);
                    $this->checkValue($field);
                    (new Meta($field))->validate();
                });
            });
    }

    private function checkFormat($section)
    {
        $valid = $section->get('fields') instanceof Obj
            && $section->get('fields')->filter(function ($field) {
                return ! $field instanceof Obj;
            })->isEmpty();

        if (! $valid) {
            throw new TemplateFormatException(__(
                'The fields attribute must be an array (of objects)'
            ));
        }
    }

    private function checkAttributes($field)
    {
        $diff = collect(Attributes::List)
            ->diff(collect($field)->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateAttributeException(__(
                'Mandatory Field Attribute(s) Missing: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkValue($field)
    {
        $meta = $field->get('meta');

        if ($meta->get('custom')) {
            return;
        }

        if ($meta->get('type') === 'input' && $meta->get('content') === 'checkbox') {
            if (! is_bool($field->get('value'))) {
                throw new TemplateValueException(__(
                    'Chexboxes must have a boolean default value: ":field"',
                    ['field' => $field->get('name')]
                ));
            }

            return;
        }

        if ($meta->get('type') === 'select'
            && $meta->get('multiple')
            && ! is_array($field->get('value'))
            && ! is_object($field->get('value'))) {
            throw new TemplateValueException(__(
                    'Multiple selects must have an array default value: ":field"',
                    ['field' => $field->get('name')]
                ));
        }
    }
}
