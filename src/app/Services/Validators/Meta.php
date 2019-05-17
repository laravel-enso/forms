<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Helpers\app\Classes\Enum;
use LaravelEnso\Forms\app\Exceptions\TemplateException;
use LaravelEnso\Forms\app\Attributes\Meta as Attributes;

class Meta
{
    private $field;
    private $meta;

    public function __construct(Obj $field)
    {
        $this->field = $field;
        $this->meta = $field->get('meta');
    }

    public function validate()
    {
        if ($this->meta->get('custom')) {
            return;
        }

        $this->checkMandatoryAttributes()
            ->checkOptionalAttributes()
            ->checkFormat()
            ->checkType();
    }

    private function checkMandatoryAttributes()
    {
        $diff = collect(Attributes::Mandatory)
            ->diff($this->meta->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Mandatory Meta Attribute(s) Missing: ":attr" from field: :field',
                ['attr' => $diff->implode('", "'), 'field' => $this->field->get('name')]
            ));
        }

        return $this;
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = collect($this->meta->keys())
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown Attribute(s) Found: ":attr" in field: :field',
                ['attr' => $diff->implode('", "'), 'field' => $this->field->get('name')]
            ));
        }

        return $this;
    }

    private function checkFormat()
    {
        if ($this->meta->get('type') === 'select'
            && self::selectMetaParameterMissing($this->field)) {
            throw new TemplateException(__(
                'Mandatory "source" or "option" meta parameter is missing for the :field select field',
                ['field' => $this->field->get('name')]
            ));
        }

        if ($this->meta->get('type') === 'input'
            && self::inputMetaParameterMissing($this->field)) {
            throw new TemplateException(__(
                'Mandatory "type" meta parameter is missing for the :field input field',
                ['field' => $this->field->geT('name')]
            ));
        }

        $options = $this->meta->get('options');
        if ($options
            && ! is_array($options)
                && ! (is_string($options) && class_exists($options)
                    && new $options instanceof Enum)
                && ! method_exists($options, 'toArray')) {
            throw new TemplateException(__(
                '"options" meta parameter for field ":field" must be an array a collection or an Enum',
                ['field' => $this->field->get('name')]
            ));
        }

        return $this;
    }

    private function checkType()
    {
        if (! collect(Attributes::Types)->contains($this->meta->type)) {
            throw new TemplateException(__(
                'Unknown Field Type Found: :type',
                ['type' => $this->meta->type]
            ));
        }
    }

    private function selectMetaParameterMissing()
    {
        return ! property_exists($this->field, 'meta')
            || (! property_exists($this->meta, 'options') && ! property_exists($this->meta, 'source'));
    }

    private function inputMetaParameterMissing()
    {
        return ! property_exists($this->field, 'meta') || ! property_exists($this->meta, 'content');
    }
}
