<?php

namespace LaravelEnso\FormBuilder\app\Classes;

use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Select\app\Classes\SelectListBuilder;

class FormBuilder
{
    private $model;
    private $template;

    public function __construct(string $template, Model $model = null)
    {
        $this->model = $model;

        $this->setTemplate($template)
            ->setLabels()
            ->setValues();
    }

    public function getData()
    {
        return json_encode($this->template);
    }

    public function setAction(string $action)
    {
        $this->template->action = strtolower($action);

        return $this;
    }

    public function setUrl(string $url)
    {
        $this->template->url = $url;

        return $this;
    }

    public function setSelectOptions(string $column, $value)
    {
        $this->getAttribute($column)
            ->config
            ->options = SelectListBuilder::buildSelectList($value);

        return $this;
    }

    public function setSelectSource(string $column, string $source)
    {
        $this->getAttribute($column)->config->source = $source;

        return $this;
    }

    public function setTitle(string $title)
    {
        $this->template->title = __($title);

        return $this;
    }

    private function setValues()
    {
        if (is_null($this->model)) {
            return $this;
        }

        collect($this->template->attributes)->each(function ($attribute) {
            if (isset($this->model->{$attribute->column})) {
                $attribute->value = $this->model->{$attribute->column};
            }
        });

        return $this;
    }

    private function setLabels()
    {
        $this->template->title = __($this->template->title);
        $this->template->storeSubmit = __($this->template->storeSubmit);
        $this->template->updateSubmit = __($this->template->updateSubmit);

        collect($this->template->attributes)->each(function ($attribute) {
            $attribute->label = __($attribute->label);
        });

        return $this;
    }

    private function setTemplate(string $template)
    {
        $this->template = json_decode(\File::get($template));

        return $this;
    }

    private function getAttribute(string $column)
    {
        return collect($this->template->attributes)->filter(function ($attribute) use ($column) {
            return $attribute->column === $column;
        })->first();
    }
}
