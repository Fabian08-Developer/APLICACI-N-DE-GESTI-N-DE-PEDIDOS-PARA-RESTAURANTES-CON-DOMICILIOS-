<?php

namespace App\Livewire\Auth;

use Livewire\Component;

class RegistrationPending extends Component
{
    public function render()
    {
        return view('livewire.auth.registration-pending')->layout('layouts.guest');
    }
}
