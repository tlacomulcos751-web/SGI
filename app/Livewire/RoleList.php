<?php

namespace App\Livewire;

use App\Models\Rol;
use Livewire\Component;

class RoleList extends Component
{
    public $roles;
    public $selectedRoleId = null;

    public function mount()
    {
        $this->roles = Rol::where('eliminado', 1)->get();
    }

    public function selectRole($id)
    {
        $this->selectedRoleId = $id;
        $this->dispatch('selectRole', $id);
    }

    public function render()
    {
        return view('livewire.role-list', [
            'selectedRoleId' => $this->selectedRoleId
        ]);
    }
}
