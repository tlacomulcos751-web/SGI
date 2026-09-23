<?php

namespace App\Livewire;

use App\Models\Rol;
use Livewire\Component;

class RoleDetails extends Component
{
    public $selectedRole = null;
    protected $listeners = ['selectRole' => 'showRole'];

    public function showRole($roleId)
    {
        $this->selectedRole = Rol::find($roleId);
    }

    public function render()
    {
        return view('livewire.role-details');
    }
}
