<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Forms\Attributes\Structure as Attributes;
use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Forms\Services\Validators\Structure;
use LaravelEnso\Helpers\Services\Obj;
use Tests\TestCase;

class StructureTest extends TestCase
{
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj($this->mockedForm());
    }

    /** @test */
    public function cannot_validate_without_mandatory_attributes()
    {
        $this->template->forget('method');

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingRootAttributes('method')->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_with_unknown_attribute()
    {
        $this->template->set('unknown_attribute', 'unknown_value');

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::unknownRootAttributes('unknown_attribute')->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_actions_format()
    {
        $this->template->set('actions', 'not Obj');

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidActionsFormat()->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_params_format()
    {
        $this->template->set('params', 'not Obj');

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidParamsFormat()->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_sections_format()
    {
        $this->template->set('sections', 'not Obj');

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidSectionFormat()->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_sections_without_mandatory_attributes()
    {
        $this->section()->forget('columns');

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingSectionAttributes('columns')->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_sections_with_unknown_attribute()
    {
        $this->section()->set('unknown_attr', 'unknown_value');

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::unknownSectionAttributes('unknown_attr')->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_sections_with_invalid_column_value()
    {
        $this->section()->set('columns', -1);

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidColumnsAttributes(
                $this->section()->get('columns'),
                (new Collection(Attributes::Columns))->implode(', ')
            )->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_custom_column_sections_with_invalid_field_column_value()
    {
        $this->section()->set('columns', 'custom');
        $this->section()->get('fields')->push(new Obj(['name' => 'field_name', 'column' => -1]));

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidFieldColumn('field_name')->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_custom_column_sections_when_field_does_not_have_column()
    {
        $this->section()->set('columns', 'custom');
        $this->section()->get('fields')->push(new Obj(['name' => 'field_name']));

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingFieldColumn('field_name')->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function failes_validation_when_tabbed_form_section_misses_tab()
    {
        $this->template->set('tabs', true);

        $structure = new Structure($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingTabAttribute(0)->getMessage()
        );

        $structure->validate();
    }

    /** @test */
    public function can_validate()
    {
        $structure = new Structure($this->template);

        $structure->validate();

        $this->assertTrue(true);
    }

    protected function mockedForm()
    {
        return [
            'sections' => [
                [
                    'fields' => [],
                    'columns' => 1,
                ]
            ],
            'method' => '',
            'routeParams' => []
        ];
    }

    protected function section()
    {
        return $this->template->get('sections')->first();
    }
}
