<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Services\Validators\Routes;
use LaravelEnso\Forms\app\Exceptions\TemplateException;

class RoutesTest extends TestCase
{
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj($this->mockedRoute());
    }

    /** @test */
    public function cannot_validate_incomplete_route_actions()
    {
        $this->template->get('actions')->push('post');

        $this->template->forget('routePrefix');

        $route = new Routes($this->template);

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage(
            TemplateException::missingRoutePrefix('post')
                ->getMessage()
        );

        $route->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_prefix_route()
    {
        $this->template->get('actions')->push('post');
        $this->template->set('routePrefix', 'not_route');

        $route = new Routes($this->template);

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage(
            TemplateException::missingRoute('not_route.post')
                ->getMessage()
        );

        $route->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_custom_routes()
    {
        $this->template->get('actions')->push('post');
        $this->template->forget('routePrefix');
        $this->template->set('routes', new Obj(['post' => 'not_route']));

        $route = new Routes($this->template);

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage(
            TemplateException::missingRoute('not_route')
                ->getMessage()
        );

        $route->validate();
    }

    /** @test */
    public function can_validate_back_action_without_route()
    {
        $this->template->set('actions', new Obj(['back']));
        $this->template->forget('routePrefix');

        $route = new Routes($this->template);

        $route->validate();

        $this->assertTrue(true);
    }

    /** @test */
    public function can_validate()
    {
        $route = new Routes($this->template);

        $route->validate();

        $this->assertTrue(true);
    }

    protected function mockedRoute(): array
    {
        Route::post('route')->name('route.post');
        Route::getRoutes()->refreshNameLookups();

        return [
            'actions' => ['post'],
            'routePrefix' => 'route',
        ];
    }
}
