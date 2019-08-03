<?php

namespace LaravelEnso\Forms\tests\Services;

use Mockery;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Helpers\app\Classes\Enum;
use LaravelEnso\Forms\app\Services\Builder;

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

        $this->assertEquals($this->testField()->get('value'), 'test_value');
    }

    /** @test */
    public function set_values_from_model()
    {
        $this->testField()->set('value', 'test_value');
        $this->testModel->test_field = 'value_from_model';

        $this->runBuilder();

        $this->assertEquals($this->testField()->get('value'), 'value_from_model');
    }

    /** @test */
    public function set_values_for_datepicker()
    {
        $this->testField()->get('meta')->set('type', 'datepicker');
        $this->testField()->get('meta')->set('format', 'm-Y-d');
        $this->testModel->test_field = new Carbon('2012-12-24');

        $this->runBuilder();

        $this->assertEquals($this->testField()->get('value'), '12-2012-24');
    }

    /** @test */
    public function set_values_for_multiple_select()
    {
        $this->testField()->get('meta')->set('type', 'select');
        $this->testField()->get('meta')->set('multiple', true);
        $this->testModel->test_field = [1, 2];

        $this->runBuilder();

        $this->assertEmpty(collect($this->testField()->get('value'))->diff([1, 2]));
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

        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('cannot')->andReturn(true);
        $this->actingAs($user);

        $this->runBuilder();

        $this->assertTrue($this->template->get('actions')->get('post')['forbidden']);
    }

    /** @test */
    public function set_meta()
    {
        $this->testField()->get('meta')->set('type', 'select');
        $this->testField()->get('meta')->set('options', FormTestEnum::class);

        $this->runBuilder();

        $this->assertEquals(
            $this->testField()->get('meta')->get('options'),
            FormTestEnum::select()
        );
    }

    protected function testField()
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
                            'meta' => []
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function runBuilder()
    {
        (new Builder(
            $this->template,
            collect($this->dirty),
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
