<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use LaravelEnso\Forms\app\Attributes\Fields as Attributes;
use LaravelEnso\Forms\app\Exceptions\Template;
use LaravelEnso\Helpers\app\Classes\Obj;

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
                $this->validateSection($section);
            });
    }

    private function checkFormat($section)
    {
        $valid = $section->get('fields') instanceof Obj
            && $section->get('fields')
                ->filter(fn($field) => ! $field instanceof Obj)
                ->isEmpty();

        if (! $valid) {
            throw Template::invalidFieldsFormat();
        }
    }

    private function validateSection($section): void
    {
        $section->get('fields')->each(fn($field) => (
            $this->checkAttributes($field)
                ->checkValue($field)
        ))
        ->filter(fn($field) => ! $field->get('meta')->get('custom'))
        ->each(fn($field) => (new Meta($field))->validate());
    }

    private function checkAttributes($field)
    {
        $diff = collect(Attributes::List)
            ->diff(collect($field)->keys());

        if ($diff->isNotEmpty()) {
            throw Template::missingFieldAttributes($diff->implode('", "'));
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
                throw Template::invalidCheckboxValue($field->get('name'));
            }

            return;
        }

        if ($meta->get('type') === 'select' && $meta->get('multiple')
            && ! is_array($field->get('value')) && ! is_object($field->get('value'))) {
            throw Template::invalidSelectValue($field->get('name'));
        }
    }
}
