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
                $route = $this->routes[$action] ?? $this->template->routePrefix.'.'.$action;

                if ($this->isForbidden($route)) {
                    return;
                }

                $button = config('enso.forms.buttons.'.$action);

                if ($action === 'create') {
                    $collector[$action] = ['button' => $button, 'route' => $route];

                    return $collector;
                }

                $path = route($route, [optional($this->model)->id], false);
                $collector[$action] = ['button' => $button, 'path' => $path];

                return $collector;
            }, []);
    }

    private function isForbidden($route)
    {
        return $this->template->authorize && !request()->user()->can('access-route', $route);
    }
}
