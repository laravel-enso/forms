<?php

namespace LaravelEnso\Forms\Services\Validators;

use Illuminate\Support\Facades\Route;
use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Helpers\Services\Obj;

class Routes
{
    private Obj $template;

    public function __construct(Obj $template)
    {
        $this->template = $template;
    }

    public function validate()
    {
        $this->actions()
            ->routes();
    }

    private function actions(): self
    {
        if ($this->template->filled('routePrefix')) {
            return $this;
        }

        $this->filteredActions()
            ->each(fn ($action) => $this->actionRouteMapping($action));

        return $this;
    }

    private function actionRouteMapping(string $action): void
    {
        if (! $this->exists($action)) {
            throw Template::missingRoutePrefix($action);
        }
    }

    private function routes(): void
    {
        $this->filteredActions()
            ->each(fn ($action) => $this->checkRoute($action));
    }

    private function checkRoute(string $action): void
    {
        $route = $this->exists($action)
            ? $this->template->get('routes')->get($action)
            : $this->template->get('routePrefix').'.'.$action;

        if (! Route::has($route)) {
            throw Template::missingRoute($route);
        }
    }

    private function exists(string $action): bool
    {
        return $this->template->has('routes')
            && $this->template->get('routes')->has($action);
    }

    private function filteredActions(): Obj
    {
        return $this->template->get('actions')
            ->reject(fn ($action) => $action === 'back');
    }
}
