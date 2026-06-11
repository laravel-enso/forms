<?php

namespace LaravelEnso\Forms\tests\Services;

use Illuminate\Support\Facades\Route;
use LaravelEnso\Forms\Services\Form;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormTest extends TestCase
{
    private string $templatePath;

    protected function tearDown(): void
    {
        if (isset($this->templatePath) && file_exists($this->templatePath)) {
            unlink($this->templatePath);
        }

        parent::tearDown();
    }

    #[Test]
    public function hides_only_the_matching_tab_when_using_a_string_argument(): void
    {
        Route::post('form')->name('test.store');
        Route::getRoutes()->refreshNameLookups();

        $form = (new Form($this->templatePath()))
            ->hideTab('Pictures')
            ->create();

        $sections = $form->get('sections');

        $this->assertFalse($sections->get(0)->get('hidden', false));
        $this->assertTrue($sections->get(1)->get('hidden', false));
    }

    private function templatePath(): string
    {
        $this->templatePath = tempnam(sys_get_temp_dir(), 'enso-form-');

        file_put_contents($this->templatePath, json_encode([
            'authorize' => false,
            'routePrefix' => 'test',
            'tabs' => true,
            'sections' => [
                [
                    'tab' => 'Details',
                    'columns' => 'custom',
                    'fields' => [
                        [
                            'label' => 'Name',
                            'name' => 'name',
                            'value' => '',
                            'column' => 12,
                            'meta' => ['type' => 'input', 'content' => 'text'],
                        ],
                    ],
                ],
                [
                    'tab' => 'Pictures',
                    'columns' => 'slot',
                    'slot' => 'pictures',
                    'fields' => [],
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        return $this->templatePath;
    }
}
