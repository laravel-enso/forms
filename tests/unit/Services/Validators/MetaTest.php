<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Services\Validators\Meta;
use LaravelEnso\Forms\app\Exceptions\TemplateValueException;
use LaravelEnso\Forms\app\Exceptions\TemplateFormatException;
use LaravelEnso\Forms\app\Exceptions\TemplateAttributeException;

class MetaTest extends TestCase
{
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj($this->mockedField());
    }

    /** @test */
    public function cannot_validate_without_mandatory_attributes()
    {
        $this->template->get('meta')->forget('type');

        $meta = new Meta($this->template);

        $this->expectException(TemplateAttributeException::class);

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_unknown_attributes()
    {
        $this->template->get('meta')->set('unknown', []);

        $meta = new Meta($this->template);

        $this->expectException(TemplateAttributeException::class);

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_select_format()
    {
        $this->template->get('meta')->set('type', 'select');

        $meta = new Meta($this->template);

        $this->expectException(TemplateFormatException::class);

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_input_format_attributes()
    {
        $this->template->get('meta')->set('type', 'input');

        $meta = new Meta($this->template);

        $this->expectException(TemplateFormatException::class);

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_options_format()
    {
        $this->template->get('meta')->set('options', 'NOT_ARRAY');

        $meta = new Meta($this->template);

        $this->expectException(TemplateFormatException::class);

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_type()
    {
        $this->template->get('meta')->set('type', 'WRONG_TYPE');

        $meta = new Meta($this->template);

        $this->expectException(TemplateValueException::class);

        $meta->validate();
    }

    /** @test */
    public function can_validate_custom_meta_without_any_other_attributes()
    {
        $meta = new Meta(new Obj(['meta' => ['custom' => true]]));

        $meta->validate();

        $this->assertTrue(true);
    }

    /** @test */
    public function can_validate()
    {
        $meta = new Meta($this->template);

        $meta->validate();

        $this->assertTrue(true);
    }

    protected function mockedField(): array
    {
        return [
            'name' => 'mocked_field',
            'meta' => [
                'type' => 'textarea'
            ]
        ];
    }
}
