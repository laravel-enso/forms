<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Services\Validators\Actions;
use LaravelEnso\Forms\app\Exceptions\TemplateException;
use LaravelEnso\Forms\app\Attributes\Actions as Attributes;

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

        $this->expectException(TemplateException::class);

        $this->expectExceptionMessage(
            TemplateException::unknownActions(
                $unknownAction,
                collect(Attributes::Create)->merge(Attributes::Update)
                    ->unique()->implode(', ')
            )->getMessage()
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
        return collect(Attributes::Create)
            ->merge(Attributes::Update)
            ->unique()
            ->values();
    }
}
