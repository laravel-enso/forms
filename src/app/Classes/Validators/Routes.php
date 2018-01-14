<?php

namespace LaravelEnso\FormBuilder\app\Classes\Validators;

use Symfony\Component\Routing\Route;
use LaravelEnso\FormBuilder\app\Exceptions\TemplateException;

class Routes
{
    private $template;

    public function __construct($template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->checkActionRouteMapping();
        $this->checkRoutes();
    }

    private function checkActionRouteMapping()
    {
        if (!is_null($this->template->routePrefix)) {
            return;
        }

        collect($this->template->actions)->each(function ($action) {
            if (!isset($this->template->routes[$action])) {
                throw new TemplateException(__(
                    '"routePrefix" attribute is missing and no route for action :action was provided',
                    ['action' => $action]
                ));
            }
        });
    }

    private function checkRoutes()
    {
        collect($this->template->actions)->each(function ($action) {
            $route = $this->template->routes[$action] ?? $this->template->routePrefix.'.'.$action;

            $this->checkRoute($route);
        });
    }

    private function checkRoute($route)
    {
        if (!\Route::has($route)) {
            throw new TemplateException(__(
                'Route does not exist: :route',
                ['route' => $route]
            ));
        }
    }
}
