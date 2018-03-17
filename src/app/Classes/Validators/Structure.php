<?php

namespace LaravelEnso\FormBuilder\app\Classes\Validators;

use LaravelEnso\FormBuilder\app\Exceptions\TemplateException;
use LaravelEnso\FormBuilder\app\Classes\Attributes\Structure as Attributes;

class Structure
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->checkMandatoryAttributes()
            ->checkOptionalAttributes()
            ->checkFormat()
            ->checkSections();
    }

    private function checkMandatoryAttributes()
    {
        $diff = collect(Attributes::Mandatory)
            ->diff(collect($this->template)->keys());

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Mandatory attribute(s) missing: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkOptionalAttributes()
    {
        $attributes = collect(Attributes::Mandatory)
            ->merge(Attributes::Optional);

        $diff = collect($this->template)
            ->keys()
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown attribute(s) found: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }

        return $this;
    }

    private function checkFormat()
    {
        if (property_exists($this->template, 'actions') && !is_array($this->template->actions)) {
            throw new TemplateException(__('"actions" attribute must be an array'));
        }

        if (property_exists($this->template, 'params') && !is_object($this->template->params)) {
            throw new TemplateException(__('"params" attribute must be an object'));
        }

        if (!is_array($this->template->sections)) {
            throw new TemplateException(__('"section" attribute must be an array'));
        }

        return $this;
    }

    private function checkSections()
    {
        $attributes = collect($this->template->sections)
            ->reduce(function ($attributes, $section) {
                return $attributes->merge(collect($section)->keys());
            }, collect())->unique()->values();

        $this->checkSectionsMandatory($attributes);
        $this->checkSectionsOptional($attributes);
    }

    private function checkSectionsMandatory($attributes)
    {
        $diff = collect(Attributes::SectionMandatory)
            ->diff($attributes);

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Mandatory attribute(s) missing from section object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }
    }

    private function checkSectionsOptional($attributes)
    {
        $diff = $attributes->diff(
            collect(Attributes::SectionMandatory)
                ->merge(Attributes::SectionOptional)
        );

        if ($diff->isNotEmpty()) {
            throw new TemplateException(__(
                'Unknown attribute(s) found in section object: ":attr"',
                ['attr' => $diff->implode('", "')]
            ));
        }
    }
}
