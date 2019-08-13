<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Services\Validators\Structure;
use LaravelEnso\Forms\app\Exceptions\TemplateValueException;
use LaravelEnso\Forms\app\Exceptions\TemplateFormatException;
use LaravelEnso\Forms\app\Exceptions\TemplateAttributeException;

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

        $this->expectException(TemplateAttributeException::class);

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_with_unknown_attribute()
    {
        $this->template->set('unknown_attribute', 'unknown_value');

        $structure = new Structure($this->template);

        $this->expectException(TemplateAttributeException::class);

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_actions_format()
    {
        $this->template->set('actions', 'not Obj');

        $structure = new Structure($this->template);

        $this->expectException(TemplateFormatException::class);

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_params_format()
    {
        $this->template->set('params', 'not Obj');

        $structure = new Structure($this->template);

        $this->expectException(TemplateFormatException::class);

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_sections_format()
    {
        $this->template->set('sections', 'not Obj');

        $structure = new Structure($this->template);

        $this->expectException(TemplateFormatException::class);

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_sections_without_mandatory_attributes()
    {
        $this->section()->forget('columns');

        $structure = new Structure($this->template);

        $this->expectException(TemplateAttributeException::class);

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_sections_with_unknown_attribute()
    {
        $this->section()->set('unknown_attr', 'unknown_value');

        $structure = new Structure($this->template);

        $this->expectException(TemplateAttributeException::class);

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_sections_with_wrong_column_value()
    {
        $this->section()->set('columns', -1);

        $structure = new Structure($this->template);

        $this->expectException(TemplateValueException::class);

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_custom_column_sections_with_wrong_field_column_value()
    {
        $this->section()->set('columns', 'custom');
        $this->section()->get('fields')->push(new Obj(['column' => -1]));

        $structure = new Structure($this->template);

        $this->expectException(TemplateValueException::class);

        $structure->validate();
    }

    /** @test */
    public function cannot_validate_custom_column_sections_when_field_does_not_have_column()
    {
        $this->section()->set('columns', 'custom');
        $this->section()->get('fields')->push(new Obj(['name' => 'field_name']));

        $structure = new Structure($this->template);

        $this->expectException(TemplateValueException::class);

        $structure->validate();
    }

    /** @test */
    public function when_form_has_tab_and_section_does_not_have_tab_then_should_not_validate()
    {
        $this->template->set('tabs', true);

        $structure = new Structure($this->template);

        $this->expectException(TemplateFormatException::class);

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
                    'columns' => 0,
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
