<?php

namespace LaravelEnso\FormBuilder\app\TestTraits;

trait EditForm
{
    /** @test */
    public function can_view_edit_form()
    {
        if (! isset($this->testModel)) {
            throw new \Exception('"testModel" property is missing from your test');
        }

        if (! isset($this->permissionGroup)) {
            throw new \Exception('"permissionGroup" property is missing from your test');
        }

        $this->testModel->save();

        $this->get(route($this->permissionGroup.'.edit', [$this->testModel->id], false))
            ->assertStatus(200)
            ->assertJsonStructure(['form']);
    }
}
