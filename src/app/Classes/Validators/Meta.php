<?php

namespace LaravelEnso\FormBuilder\app\Classes\Validators;

use Illuminate\Database\Eloquent\Collection;
use LaravelEnso\FormBuilder\app\Exceptions\TemplateException;
use LaravelEnso\FormBuilder\app\Classes\Attributes\Meta as Attributes;

class Meta
{
    private $field;

    public function __construct($field)
    {
        $this->field = $field;
    }

    public function validate()
    {
        if ($this->isCustom()) {
            return;
        }

        $this->checkMandatoryAttributes()
            ->checkOptionalAttributes()
            ->checkFormat()
            ->checkType();
    }

    private function isCustom()
    {
        return property_exists($this->field->meta, 'custom')
            && $this->field->meta->custom;
    }

    private function checkMandatoryAttributes()
    {
        $diff = collect(Attributes::Mandatory)
            ->diff(collect($this->field->meta)->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Mandatory Meta Attribute(s) Missing: ":attr" from field: :field',
                ['attr' => $diff->implode('", "'), 'field' => $this->field->name]
            ));
        }

        return $this;
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = collect($this->field->meta)
            ->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown Attribute(s) Found: ":attr" in field: :field',
                ['attr' => $diff->implode('", "'), 'field' => $this->field->name]
            ));
        }

        return $this;
    }

    private function checkFormat()
    {
        if ($this->field->meta->type === 'select' && self::selectMetaParameterMissing($this->field)) {
            throw new TemplateException(__(
                'Mandatory "source" or "option" meta parameter is missing for the :field select field',
                ['field' => $this->field->name]
            ));
        }

        if ($this->field->meta->type === 'input' && self::inputMetaParameterMissing($this->field)) {
            throw new TemplateException(__(
                'Mandatory "type" meta parameter is missing for the :field input field',
                ['field' => $this->field->name]
            ));
        }
        if (property_exists($this->field->meta, 'options')
            && !is_array($this->field->meta->options)
            && !method_exists($this->field->meta->options, 'toArray')) {
            throw new TemplateException(__(
                '"options" meta parameter for field :field must be an array or an collection',
                ['field' => $this->field->name]
            ));
        }

        return $this;
    }

    private function checkType()
    {
        if (!collect(Attributes::Types)->contains($this->field->meta->type)) {
            throw new TemplateException(__(
                'Unknown Field Type Found: :type',
                ['type' => $this->field->type]
            ));
        }
    }

    private function selectMetaParameterMissing()
    {
        return !property_exists($this->field, 'meta')
            || (!property_exists($this->field->meta, 'options') && !property_exists($this->field->meta, 'source'));
    }

    private function inputMetaParameterMissing()
    {
        return !property_exists($this->field, 'meta') || !property_exists($this->field->meta, 'content');
    }
}
