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
    }

    public function run()
    {
        $this->appendConfigParams();

        $this->setValues();

        $this->computeActions();

        unset($this->template->routes, $this->template->routePrefix, $this->template->authorize);
    }

    private function setValues()
    {
        if (!$this->model) {
            return $this;
        }

        collect($this->template->sections)->each(function ($section) {
            collect($section->fields)->each(function ($field) {
                $field->value = $this->model->{$field->name};
            });
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

    private function appendConfigParams()
    {
        $this->template->authorize = isset($this->template->authorize)
            ? $this->template->authorize
            : config('enso.forms.authorize');

        $this->template->dividerTitlePlacement = isset($this->template->dividerTitlePlacement)
            ? $this->template->dividerTitlePlacement
            : config('enso.forms.dividerTitlePlacement');
    }

    private function isForbidden($route)
    {
        return $this->template->authorize && !request()->user()->can('access-route', $route);
    }
}
