<?php

namespace LaravelEnso\Forms\tests\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use LaravelEnso\Enums\Services\Enum;
use LaravelEnso\Forms\Services\Builder;
use LaravelEnso\Helpers\Services\Obj;
use Mockery;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    private $template;
    private $testModel;
    private $dirty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj($this->mockedForm());
        $this->testModel = new FormTestModel();
        $this->dirty = [];
    }

    /** @test */
    public function set_default_on_template()
    {
        $this->runBuilder();

        $this->assertEquals(
            $this->template->get('dividerTitlePlacement'),
            config('enso.forms.dividerTitlePlacement')
        );

        $this->assertEquals($this->template->get('labels'), config('enso.forms.labels'));
    }

    /** @test */
    public function set_values_from_dirty()
    {
        $this->dirty = ['test_field'];

        $this->runBuilder();

        $this->assertEquals($this->field()->get('value'), 'test_value');
    }

    /** @test */
    public function set_values_from_model()
    {
        $this->field()->set('value', 'test_value');
        $this->testModel->test_field = 'value_from_model';

        $this->runBuilder();

        $this->assertEquals($this->field()->get('value'), 'value_from_model');
    }

    /** @test */
    public function set_values_for_datepicker()
    {
        $this->field()->get('meta')->set('type', 'datepicker');
        $this->field()->get('meta')->set('altFormat', 'm-Y-d');
        $this->testModel->test_field = new Carbon('2012-12-24');

        $this->runBuilder();

        $this->assertEquals($this->field()->get('value'), $this->testModel->test_field->format('Y-m-d'));
    }

    /** @test */
    public function set_values_for_multiple_select()
    {
        $this->field()->get('meta')->set('type', 'select');
        $this->field()->get('meta')->set('multiple', true);
        $this->testModel->test_field = [1, 2];

        $this->runBuilder();

        $this->assertEmpty((new Collection($this->field()->get('value')))->diff([1, 2]));
    }

    /** @test */
    public function set_actions()
    {
        Route::post('route')->name('test.post');
        Route::getRoutes()->refreshNameLookups();

        $this->template->get('actions')->push('post');
        $this->template->set('routePrefix', 'test');
        $this->template->set('authorize', false);

        $this->runBuilder();

        $action = $this->template->get('actions')->get('post');

        $this->assertEquals($action['button'], config('enso.forms.buttons.post'));
        $this->assertFalse($action['forbidden']);
        $this->assertEquals($action['path'], '/route');
    }

    /** @test */
    public function set_actions_with_authorize()
    {
        Route::post('route')->name('test.post');
        Route::getRoutes()->refreshNameLookups();

        $this->template->get('actions')->push('post');
        $this->template->set('routePrefix', 'test');
        $this->template->set('authorize', true);

        $user = Mockery::mock(config('auth.providers.users.model'))->makePartial();
        $user->shouldReceive('cannot')->andReturn(true);
        $this->actingAs($user);

        $this->runBuilder();

        $this->assertTrue($this->template->get('actions')->get('post')['forbidden']);
    }

    /** @test */
    public function set_meta()
    {
        $this->field()->get('meta')->set('type', 'select');
        $this->field()->get('meta')->set('options', FormTestEnum::class);

        $this->runBuilder();

        $this->assertEquals(
            $this->field()->get('meta')->get('options'),
            FormTestEnum::select()
        );
    }

    protected function field()
    {
        return $this->template->get('sections')->first()->get('fields')->first();
    }

    protected function mockedForm()
    {
        return [
            'actions' => [],
            'sections' => [
                [
                    'fields' => [
                        [
                            'name' => 'test_field',
                            'value' => 'test_value',
                            'meta' => [],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function runBuilder()
    {
        (new Builder(
            $this->template,
            new Collection($this->dirty),
            $this->testModel
        ))->run();
    }
}

class FormTestModel extends Model
{
    public $test_field;
}

class FormTestEnum extends Enum
{
    public const Active = 1;
    public const InActive = 0;
}
