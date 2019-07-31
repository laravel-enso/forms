<?php


namespace LaravelEnso\Forms\tests\Services;


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
        $this->testModel = new TestModel();
        $this->dirty = [];
    }

    /** @test */
    public function set_default_on_template()
    {
        $this->runBuilder();

        $this->assertEquals($this->template->get('dividerTitlePlacement'), config('enso.forms.dividerTitlePlacement'));
        $this->assertEquals($this->template->get('labels'), config('enso.forms.labels'));
    }

    /** @test */
    public function set_values_from_dirty()
    {
        $this->dirty = ['test_field'];

        $this->runBuilder();

        $this->assertEquals($this->firstField()->get('value'), 'test_value');
    }


    /** @test */
    public function set_values_from_model()
    {
        $this->firstField()->set('value', 'test_value');
        $this->testModel->test_field = 'value_from_model';

        $this->runBuilder();

        $this->assertEquals($this->firstField()->get('value'), 'value_from_model');
    }

    /** @test */
    public function set_values_for_datepicker()
    {
        $this->firstField()->get('meta')->set('type', 'datepicker');
        $this->firstField()->get('meta')->set('format', 'm-Y-d');
        $this->testModel->test_field = new Carbon('2012-12-24');

        $this->runBuilder();

        $this->assertEquals($this->firstField()->get('value'), '12-2012-24');
    }

    /** @test */
    public function set_values_for_multiple_select()
    {
        $this->firstField()->get('meta')->set('type', 'select');
        $this->firstField()->get('meta')->set('multiple', true);
        $this->testModel->test_field = collect([
            ['id' => 1, 'name' => 'test1'],
            ['id' => 2, 'name' => 'test2'],
        ]);

        $this->runBuilder();

        $this->assertEmpty($this->firstField()->get('value')->diff([1, 2]));
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

        $this->assertEquals($this->postAction()['button'], config('enso.forms.buttons.post'));
        $this->assertEquals($this->postAction()['forbidden'], false);
        $this->assertEquals($this->postAction()['path'], '/route');
    }

    /** @test */
    public function set_actions_with_authorize()
    {
        Route::post('route')->name('test.post');
        Route::getRoutes()->refreshNameLookups();

        $this->template->get('actions')->push('post');
        $this->template->set('routePrefix', 'test');
        $this->template->set('authorize', true);

        $user = \Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('cannot')->andReturn(true);
        $this->actingAs($user);

        $this->runBuilder();

        $this->assertEquals($this->postAction()['forbidden'], true);
    }

    /** @test */
    public function set_meta()
    {
        $this->firstField()->get('meta')->set('type', 'select');
        $this->firstField()->get('meta')->set('options', TestEnum::class);

        $this->runBuilder();

        $this->assertEquals(
            $this->firstField()->get('meta')->get('options'),
            TestEnum::select()
        );
    }

    protected function firstField()
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
        $builder = new Builder($this->template, collect($this->dirty), $this->testModel);
        $builder->run();
    }

    /**
     * @return mixed
     */
    protected function postAction()
    {
        return $this->template->get('actions')->get('post');
    }


}

class TestModel extends Model
{
    public $test_field;
}

class TestEnum extends Enum
{
    public const Active = 1;
    public const InActive = 0;
}
