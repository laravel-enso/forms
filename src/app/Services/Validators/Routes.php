<?php

namespace LaravelEnso\Forms\app\Services\Validators;

use Route;
use LaravelEnso\Forms\app\Exceptions\Template;
use LaravelEnso\Helpers\app\Classes\Obj;

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

        $this->template->get('actions')
            ->each(function ($action) {
                if ($action !== 'back' && (! $this->template->has('routes')
                    || ! $this->template->get('routes')->has($action))) {
                    throw Template::missingRoutePrefix($action);
                }
            });

        return $this;
    }

    private function checkRoutes()
    {
        $this->template->get('actions')
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
        if (! Route::has($route)) {
            throw Template::missingRoute($route);
        }
    }
}
