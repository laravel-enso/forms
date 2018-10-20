<?php

namespace LaravelEnso\FormBuilder\app\TestTraits;

trait DestroyForm
{
    /** @test */
    public function can_destroy_model()
    {
        if (! isset($this->testModel)) {
            throw new \Exception('"testModel" property is missing from your test');
        }

        if (! isset($this->permissionGroup)) {
            throw new \Exception('"permissionGroup" property is missing from your test');
        }

        $this->testModel->save();

        $this->delete(route($this->permissionGroup.'.destroy', [$this->testModel->id], false))
            ->assertStatus(200)
            ->assertJsonStructure(['message', 'redirect']);
    }
}
