<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Forms\Attributes\Fields as Attributes;
use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Forms\Services\Validators\Fields;
use LaravelEnso\Helpers\Services\Obj;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FieldsTest extends TestCase
{
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj($this->mockedForm());
    }

    #[Test]
    public function cannot_validate_with_invalid_field_format()
    {
        $this->template->get('sections')->first()->get('fields')->push('');

        $fields = new Fields($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidFieldsFormat()->getMessage()
        );

        $fields->validate();
    }

    #[Test]
    public function cannot_validate_without_mandatory_attribute()
    {
        $this->field()->forget('label');

        $fields = new Fields($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingFieldAttributes('label')->getMessage()
        );

        $fields->validate();
    }

    #[Test]
    public function cannot_validate_with_invalid_checkbox_value()
    {
        $this->field()->set('value', 'NOT_BOOL');
        $this->field()->get('meta')->set('type', 'input');
        $this->field()->get('meta')->set('content', 'checkbox');

        $fields = new Fields($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidCheckboxValue(
                $this->field()['name']
            )->getMessage()
        );

        $fields->validate();
    }

    #[Test]
    #[DataProvider('booleanValues')]
    public function can_validate_boolean_inputs(string $content, bool $value): void
    {
        $this->field()->set('name', 'enabled');
        $this->field()->set('value', $value);
        $this->field()->get('meta')->set('type', 'input');
        $this->field()->get('meta')->set('content', $content);

        (new Fields($this->template))->validate();

        $this->assertSame($value, $this->field()->get('value'));
        $this->assertSame($content, $this->field()->get('meta')->get('content'));
    }

    public static function booleanValues(): array
    {
        return [
            ['checkbox', true], ['checkbox', false],
            ['switch', true], ['switch', false],
        ];
    }

    #[Test]
    #[DataProvider('invalidSwitchValues')]
    public function cannot_validate_invalid_switch_values(mixed $value): void
    {
        $this->field()->set('name', 'enabled');
        $this->field()->set('value', $value);
        $this->field()->get('meta')->set('type', 'input');
        $this->field()->get('meta')->set('content', 'switch');

        $this->expectException(Template::class);
        $this->expectExceptionMessage(Template::invalidSwitchValue('enabled')->getMessage());

        (new Fields($this->template))->validate();
    }

    public static function invalidSwitchValues(): array
    {
        return [[null], [0], [1], ['false'], ['true'], [[]]];
    }

    #[Test]
    public function can_validate_custom_switch_values(): void
    {
        $this->field()->set('value', 'custom');
        $this->field()->get('meta')->set('type', 'input');
        $this->field()->get('meta')->set('content', 'switch');
        $this->field()->get('meta')->set('custom', true);

        (new Fields($this->template))->validate();

        $this->assertSame('custom', $this->field()->get('value'));
    }

    #[Test]
    public function can_validate_custom_meta_and_invalid_values()
    {
        $this->field()->set('value', 'NOT_BOOL');
        $this->field()->get('meta')->set('type', 'input');
        $this->field()->get('meta')->set('content', 'checkbox');
        $this->field()->get('meta')->set('custom', true);

        $fields = new Fields($this->template);

        $fields->validate();

        $this->assertTrue(true);
    }

    #[Test]
    public function cannot_validate_with_invalid_multiple_select_value()
    {
        $this->field()->set('value', 'NOT_ARRAY');
        $this->field()->get('meta')->set('type', 'select');
        $this->field()->get('meta')->set('multiple', true);

        $fields = new Fields($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::invalidSelectValue(
                $this->field()['name']
            )->getMessage()
        );

        $fields->validate();
    }

    #[Test]
    public function can_validate()
    {
        $fields = new Fields($this->template);

        $fields->validate();

        $this->assertTrue(true);
    }

    protected function mockedForm(): array
    {
        $field = Collection::wrap(Attributes::List)
            ->reduce(fn ($field, $attribute) => $field->put($attribute, new Obj()), new Obj());

        $field->get('meta')->set('type', 'textarea');

        return [
            'sections' => [
                ['fields' => [$field]],
            ],
        ];
    }

    protected function field()
    {
        return $this->template->get('sections')->first()->get('fields')->first();
    }
}
