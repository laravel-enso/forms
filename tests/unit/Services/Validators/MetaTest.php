<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use LaravelEnso\Forms\Exceptions\Template;
use LaravelEnso\Forms\Services\Validators\Meta;
use LaravelEnso\Helpers\Services\Obj;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MetaTest extends TestCase
{
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj($this->mockedField());
    }

    #[Test]
    public function cannot_validate_without_mandatory_attributes()
    {
        $this->template->get('meta')->forget('type');

        $meta = new Meta($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingMetaAttributes(
                $this->mockedField()['name'],
                'type'
            )->getMessage()
        );

        $meta->validate();
    }

    #[Test]
    public function cannot_validate_with_unknown_attributes()
    {
        $this->template->get('meta')->set('unknown', []);

        $meta = new Meta($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::unknownMetaAttributes(
                $this->mockedField()['name'],
                'unknown'
            )->getMessage()
        );

        $meta->validate();
    }

    #[Test]
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

    #[Test]
    public function cannot_validate_with_missing_input_attribute()
    {
        $this->template->get('meta')->set('type', 'input');

        $meta = new Meta($this->template);

        $this->expectException(Template::class);

        $this->expectExceptionMessage(
            Template::missingInputContent(
                $this->mockedField()['name']
            )->getMessage()
        );

        $meta->validate();
    }

    #[Test]
    #[DataProvider('supportedInputContent')]
    public function can_validate_supported_input_content(string $content): void
    {
        $this->template->get('meta')->set('type', 'input');
        $this->template->get('meta')->set('content', $content);

        (new Meta($this->template))->validate();

        $this->assertSame($content, $this->template->get('meta')->get('content'));
    }

    public static function supportedInputContent(): array
    {
        return [
            ['text'], ['number'], ['email'], ['password'],
            ['encrypt'], ['checkbox'], ['switch'], ['money'],
        ];
    }

    #[Test]
    #[DataProvider('unsupportedInputContent')]
    public function cannot_validate_unsupported_input_content(mixed $content): void
    {
        $this->template->get('meta')->set('type', 'input');
        $this->template->get('meta')->set('content', $content);

        $this->expectException(Template::class);
        $this->expectExceptionMessage(Template::invalidInputContent('mocked_field')->getMessage());

        (new Meta($this->template))->validate();
    }

    public static function unsupportedInputContent(): array
    {
        return [['unsupported'], ['date'], ['TEXT'], [''], [null], [true], [0], [[]]];
    }

    #[Test]
    public function cannot_validate_password_as_a_field_type(): void
    {
        $this->template->get('meta')->set('type', 'password');

        $this->expectException(Template::class);
        $this->expectExceptionMessage(Template::invalidFieldType('password')->getMessage());

        (new Meta($this->template))->validate();
    }

    #[Test]
    #[DataProvider('customInputMeta')]
    public function can_validate_custom_input_meta(array $attributes): void
    {
        $this->template->set('meta', new Obj(['custom' => true, ...$attributes]));

        (new Meta($this->template))->validate();

        $this->assertTrue($this->template->get('meta')->get('custom'));
    }

    public static function customInputMeta(): array
    {
        return [
            [['type' => 'input', 'content' => 'custom-content']],
            [['type' => 'input']],
            [['type' => 'password']],
        ];
    }

    #[Test]
    public function does_not_validate_content_for_non_input_fields(): void
    {
        $this->template->get('meta')->set('content', 'custom-content');

        (new Meta($this->template))->validate();

        $this->assertSame('textarea', $this->template->get('meta')->get('type'));
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function can_validate_custom_meta_without_any_other_attributes()
    {
        $meta = new Meta(new Obj(['meta' => ['custom' => true]]));

        $meta->validate();

        $this->assertTrue(true);
    }

    #[Test]
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
                'type' => 'textarea',
            ],
        ];
    }
}
