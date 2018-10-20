<?php

namespace LaravelEnso\FormBuilder\app\Classes;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class Builder
{
    private $template;
    private $model;
    private $dirty;

    public function __construct($template, Collection $dirty, Model $model = null)
    {
        $this->template = $template;
        $this->model = $model;
        $this->dirty = $dirty;
    }

    public function run()
    {
        $this->appendConfigParams()
            ->setValues()
            ->computeActions()
            ->computeSelects();

        unset($this->template->routes, $this->template->routePrefix, $this->template->authorize);
    }

    private function setValues()
    {
        if (! $this->model) {
            return $this;
        }

        collect($this->template->sections)->each(function ($section) {
            collect($section->fields)->each(function ($field) {
                if (! $this->dirty->contains($field->name)) {
                    $field->value = $this->value($field);
                }
            });
        });

        return $this;
    }

    private function value($field)
    {
        return $field->meta->type === 'datepicker'
            && is_object($this->model->{$field->name})
            && $this->model->{$field->name} instanceof Carbon
            ? $this->model->{$field->name}
                ->format($this->dateFormat($field))
            : $this->model->{$field->name};
    }

    private function computeActions()
    {
        $this->template->actions = collect($this->template->actions)
            ->reduce(function ($collector, $action) {
                $actionConfig = [];
                $actionConfig['button'] = config('enso.forms.buttons.'.$action);
                $route = $this->routes[$action]
                    ?? $this->template->routePrefix.'.'.$action;
                $actionConfig['forbidden'] = $this->isForbidden($route);

                [$routeOrPath, $value] = collect(['create', 'show', 'back'])->contains($action)
                    ? ['route', $route]
                    : ['path', route($route, is_null($this->model) ? [] : [$this->model->getKey()], false)];

                $actionConfig[$routeOrPath] = $value;

                if ($action === 'show') {
                    $actionConfig['id'] = $this->model->getKey();
                }

                $collector[$action] = $actionConfig;

                return $collector;
            }, []);

        return $this;
    }

    private function computeSelects()
    {
        collect($this->template->sections)->each(function ($section) {
            collect($section->fields)->each(function ($field) {
                if ($field->meta->type === 'select'
                    && property_exists($field->meta, 'options')
                    && is_string($field->meta->options)) {
                    $field->meta->options = $field->meta->options::select();
                }
            });
        });
    }

    private function appendConfigParams()
    {
        if (! property_exists($this->template, 'authorize')) {
            $this->template->authorize = config('enso.forms.authorize');
        }

        if (! property_exists($this->template, 'dividerTitlePlacement')) {
            $this->template->dividerTitlePlacement = config('enso.forms.dividerTitlePlacement');
        }

        return $this;
    }

    private function dateFormat($field)
    {
        if (! property_exists($field->meta, 'format')) {
            $field->meta->format = config('enso.forms.dateFormat');
        }

        return $field->meta->format;
    }

    private function isForbidden($route)
    {
        return $route !== 'back'
            && $this->template->authorize
            && request()->user()
                ->cannot('access-route', $route);
    }
}
