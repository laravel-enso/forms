<?php

namespace LaravelEnso\FormBuilder\app\Classes;

use LaravelEnso\Helpers\Classes\Obj;
use Illuminate\Database\Eloquent\Model;

class FormBuilder
{
    const AllowedMethods = ['post', 'put', 'patch'];
    const AllowedActions = ['create', 'store', 'update', 'destroy'];

    private $buttonLabels = [
        'create' => 'Add',
        'store' => 'Create',
        'update' => 'Update',
        'destroy' => 'Delete',
    ];

    private $actions;
    private $routes = [];
    private $model;
    private $template;
    private $hasRouteAccessCheck = true;

    public function __construct(string $template, Model $model = null)
    {
        $this->model = $model;

        $this->setTemplate($template)
            ->setValues();

        $this->template->actions = new Obj();
    }

    public function getData()
    {
        $this->run();

        return $this->template;
    }

    public function setMethod(string $method)
    {
        $this->template->method = $method;

        return $this;
    }

    public function setActions(array $actions)
    {
        collect($actions)->each(function ($action) {
            $this->validateAction($action);
        });

        $this->actions = $actions;

        return $this;
    }

    public function setPrefix(string $prefix)
    {
        $this->template->prefix = $prefix;

        return $this;
    }

    public function setTitle(string $title)
    {
        $this->template->title = $title;

        return $this;
    }

    public function setIcon(string $icon)
    {
        $this->template->icon = $icon;

        return $this;
    }

    public function setRoute(string $action, string $route)
    {
        $this->validateAction($action);

        if ($this->hasRouteAccessCheck && !request()->user()->can('access-route', $route)) {
            return $this;
        }

        $this->routes[$action] = $route;

        return $this;
    }

    public function setButtonLabel(string $action, string $label)
    {
        $this->validateAction($action);
        $this->buttonLabels[$action] = $label;

        return $this;
    }

    public function setSelectOptions(string $column, $value)
    {
        $this->getField($column)->meta->options = $value;

        return $this;
    }

    public function setSelectSource(string $column, string $source)
    {
        $this->getField($column)->meta->source = $source;

        return $this;
    }

    public function setValue(string $column, $value)
    {
        $this->getField($column)->value = $value;

        return $this;
    }

    public function setMetaParam(string $column, string $param, $value)
    {
        $this->getField($column)->meta->{$param} = $value;

        return $this;
    }

    public function disableRouteAccessCheck()
    {
        $this->hasRouteAccessCheck = false;
    }

    private function setTemplate(string $template)
    {
        $this->template = json_decode(\File::get($template));

        if (!$this->template) {
            throw new \EnsoException('Template is not readable');
        }

        return $this;
    }

    private function setValues()
    {
        if (is_null($this->model)) {
            return $this;
        }

        collect($this->template->fields)->each(function ($field) {
            if (isset($this->model->{$field->column})) {
                $field->value = $this->model->{$field->column};
            }
        });

        return $this;
    }

    private function getField(string $column)
    {
        $field = collect($this->template->fields)->filter(function ($field) use ($column) {
            return $field->column === $column;
        })->first();

        if (!$field) {
            throw new \EnsoException(__('The following field is missing from the JSON template').': '.$column);
        }

        return $field;
    }

    private function run()
    {
        $this->validateMethod();
        $this->actions = $this->actions ?: $this->getDefaultActions();
        $this->setRoutes();
        $this->buildActions();
    }

    private function getDefaultActions()
    {
        return $this->template->method === 'post'
            ? ['store']
            : ['create', 'update', 'destroy'];
    }

    private function setRoutes()
    {
        collect($this->actions)->each(function ($action) {
            if (!isset($this->routes[$action])) {
                $this->validatePrefix();
                $this->routes[$action] = $this->template->prefix.'.'.$action;
            }
        });
    }

    private function buildActions()
    {
        $this->template->actions = new Obj();

        collect($this->actions)->each(function ($action) {
            $this->template->actions->set(
                $action,
                new Obj([
                    'label' => $this->buttonLabels[$action],
                    'route' => $this->routes[$action],
                    'id' => optional($this->model)->id,
                ])
            );
        });
    }

    private function validatePrefix()
    {
        if (!$this->template->prefix) {
            throw new \EnsoException('Prefix is required in order to generate the routes');
        }
    }

    private function validateMethod()
    {
        if (!$this->template->method) {
            throw new \EnsoException(__("The 'method' is required"));
        }

        $this->template->method = strtolower($this->template->method);

        if (!collect(self::AllowedMethods)->contains($this->template->method)) {
            throw new \EnsoException(__("The 'method' is incorrect. Allowed values are: 'POST', 'PATCH' or 'PUT'"));
        }

        if ($this->template->method !== 'post' && !$this->model) {
            throw new \EnsoException(__("The 'model' is missing"));
        }
    }

    private function validateAction($action)
    {
        if (!collect(self::AllowedActions)->contains($action)) {
            throw new \EnsoException(__("Incorrect action(s) provided. Allowed actions are: 'create', 'store', 'update' and 'delete'"));
        }
    }
}
