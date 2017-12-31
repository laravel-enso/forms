<?php

namespace LaravelEnso\FormBuilder\app\Classes\Validators;

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
            throw new TemplateException(__(sprintf(
                'Mandatory Meta Attribute(s) Missing: "%s" from field: "%s"',
                $diff->implode('", "'),
                $this->field->name
            )));
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
            throw new TemplateException(__(sprintf(
                'Unknown Attribute(s) Found: "%s" in field: "%s"',
                $diff->implode('", "'),
                $this->field->name
            )));
        }

        return $this;
    }

    private function checkFormat()
    {
        if ($this->field->meta->type === 'select' && self::selectMetaParameterMissing($this->field)) {
            throw new TemplateException(__(sprint(
                'Mandatory "source" or "option" meta parameter is missing for the "%s" select field',
                $this->field->name
            )));
        }

        if ($this->field->meta->type === 'input' && self::inputMetaParameterMissing($this->field)) {
            throw new TemplateException(__(sprintf(
                'Mandatory "type" meta parameter is missing for the "%s" input field',
                $this->field->name
            )));
        }

        if (property_exists($this->field->meta, 'options') && !is_object($this->field->meta->options)) {
            throw new TemplateException(__(sprintf(
                '"options" meta parameter for field %s is must be an object',
                $this->field->name
            )));
        }

        return $this;
    }

    private function checkType()
    {
        if (!$this->types->contains($this->field->type)) {
            throw new TemplateException(__(sprintf(
                'Unknown Field Type Found: "%s"',
                $this->field->type
            )));
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
