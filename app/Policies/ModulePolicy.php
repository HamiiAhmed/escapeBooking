<?php

namespace App\Policies;

use App\Models\{User, Module};

class ModulePolicy
{
    public function view(User $user, Module $module)
    {
        return $user->role->permissions->where('module_id', $module->id)->first()->can_view ?? false;
    }

    public function create(User $user, Module $module)
    {
        return $user->role->permissions->where('module_id', $module->id)->first()->can_create ?? false;
    }

    public function update(User $user, Module $module)
    {
        return $user->role->permissions->where('module_id', $module->id)->first()->can_update ?? false;
    }

    public function delete(User $user, Module $module)
    {
        return $user->role->permissions->where('module_id', $module->id)->first()->can_delete ?? false;
    }
}
