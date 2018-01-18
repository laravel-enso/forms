<?php

namespace LaravelEnso\FormBuilder\app\Classes;

use Illuminate\Database\Eloquent\Model;

class Builder
{
    private $template;
    private $model;

    public function __construct($template, Model $model = null)
    {
        $this->template = $template;
        $this->model = $model;
        $this->template->authorize = isset($this->template->authorize)
            ? $this->template->authorize
            : config('enso.forms.authorize');
    }

    public function run()
    {
        $this->setValues();

        $this->computeActions();

        unset($this->template->routes, $this->template->routePrefix, $this->template->authorize);
    }

    private function setValues()
    {
        if (!$this->model) {
            return $this;
        }

        collect($this->template->fields)->each(function ($field) {
            if (isset($this->model->{$field->name})) {
                $field->value = $this->model->{$field->name};
            }
        });

        return $this;
    }

    private function computeActions()
    {
        $this->template->actions = collect($this->template->actions)
            ->reduce(function ($collector, $action) {
                $actionConfig = [];
                $actionConfig['button'] = config('enso.forms.buttons.'.$action);
                $route = $this->routes[$action] ?? $this->template->routePrefix.'.'.$action;
                $actionConfig['forbidden'] = $this->isForbidden($route);

                [$routeOrPath, $value] = $action === 'create'
                    ? ['route', $route]
                    : ['path', route($route, is_null($this->model) ? [] : [$this->model->id], false)];

                $actionConfig[$routeOrPath] = $value;
                $collector[$action] = $actionConfig;

                return $collector;
            }, []);
    }

    private function isForbidden($route)
    {
        return $this->template->authorize && !request()->user()->can('access-route', $route);
    }
}
