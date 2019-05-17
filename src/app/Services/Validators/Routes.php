<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Exceptions\TemplateException;

class Routes
{
    private $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->checkActionRouteMapping()
            ->checkRoutes();
    }

    private function checkActionRouteMapping()
    {
        if ($this->template->filled('routePrefix')) {
            return $this;
        }

        collect($this->template->get('actions'))
            ->each(function ($action) {
                if ($action !== 'back' && (! $this->template->has('routes')
                    || ! $this->template->get('routes')->has('action'))) {
                    throw new TemplateException(__(
                        '"routePrefix" attribute is missing and no route for action :action was provided',
                        ['action' => $action]
                    ));
                }
            });

        return $this;
    }

    private function checkRoutes()
    {
        collect($this->template->get('actions'))
            ->each(function ($action) {
                if ($action !== 'back') {
                    $route = $this->template->has('routes')
                        && $this->template->get('routes')->has($action)
                            ? $this->template->get('routes')->get($action)
                            : $this->template->get('routePrefix').'.'.$action;

                    $this->checkRoute($route);
                }
            });
    }

    private function checkRoute($route)
    {
        if (! \Route::has($route)) {
            throw new TemplateException(__(
                'Route does not exist: :route',
                ['route' => $route]
            ));
        }
    }
}
