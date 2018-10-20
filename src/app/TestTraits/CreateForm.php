<?php

namespace LaravelEnso\FormBuilder\app\TestTraits;

trait CreateForm
{
    /** @test */
    public function can_view_create_form()
    {
        if (! isset($this->permissionGroup)) {
            throw new \Exception('"permissionGroup" property is missing from your test');
        }

        $this->get(route($this->permissionGroup.'.create', [], false))
            ->assertStatus(200)
            ->assertJsonStructure(['form']);
    }
}
