<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Forms\Services\Validators\Meta;
use LaravelEnso\Helpers\Services\Obj;
use Tests\TestCase;

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

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingMetaAttributes(
                $this->mockedField()['name'], 'type'
            )->getMessage()
        );

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_unknown_attributes()
    {
        $this->template->get('meta')->set('unknown', []);

        $meta = new Meta($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::unknownMetaAttributes(
                $this->mockedField()['name'], 'unknown'
            )->getMessage()
        );

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_missing_select_meta_attribute()
    {
        $this->template->get('meta')->set('type', 'select');

        $meta = new Meta($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingSelectMetaAttribute(
                $this->mockedField()['name']
            )->getMessage()
        );

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_missing_input_attribute()
    {
        $this->template->get('meta')->set('type', 'input');

        $meta = new Meta($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingInputAttribute(
                $this->mockedField()['name']
            )->getMessage()
        );

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_options_format()
    {
        $this->template->get('meta')->set('type', 'select');
        $this->template->get('meta')->set('options', 'NOT_ARRAY');

        $meta = new Meta($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidSelectOptions(
                $this->mockedField()['name']
            )->getMessage()
        );

        $meta->validate();
    }

    /** @test */
    public function cannot_validate_with_invalid_type()
    {
        $invalidType = 'INVALID_TYPE';
        $this->template->get('meta')->set('type', $invalidType);

        $meta = new Meta($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidFieldType($invalidType)->getMessage()
        );

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
