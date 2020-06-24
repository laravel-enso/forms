<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use Illuminate\Support\Collection;
use Tests\TestCase;
use LaravelEnso\Helpers\Services\Obj;
use LaravelEnso\Forms\Services\Validators\Actions;
use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Forms\Attributes\Actions as Attributes;

class ActionTest extends TestCase
{
    private $template;

    public function setUp(): void
    {
        parent::setup();

        $this->template = new Obj(['actions' => $this->mockedActions()]);
    }

    /** @test */
    public function cannot_validate_with_invalid_action()
    {
        $unknownAction = 'UNKNOWN_ACTION';

        $this->template->get('actions')->push($unknownAction);

        $action = new Actions($this->template);

        $this->expectException(Template::class);

        $actions = (new Collection(Attributes::Create))
            ->merge(Attributes::Update)->unique()->implode(', ');

        $this->expectExceptionMessage(
            Template::unknownActions($unknownAction, $actions)->getMessage()
        );

        $action->validate();
    }

    /** @test */
    public function can_validate()
    {
        $action = new Actions($this->template);

        $action->validate();

        $this->assertTrue(true);
    }

    private function mockedActions()
    {
        return (new Collection(Attributes::Create))
            ->merge(Attributes::Update)
            ->unique()
            ->values();
    }
}
