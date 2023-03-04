<?php

namespace App\Http\Livewire\Backend;

use Livewire\Component;
use App\Models\User;

class Users extends Component
{

    public $users;
    public $selectedUser;
    public $firstname;
    public $lastname;
    public $email;
    public $description;
    public $image;

    public function mount()
    {
        $this->users = User::all();
    }

    public function selectUser($user)
    {
        $this->selectedUser = $user;
        $this->firstname = $user->firstname;
        $this->lastname = $user->lastname;
        $this->email = $user->email;
        $this->description = $user->description;
        $this->image = $user->image;
    }

    public function saveUser()
    {
        $this->selectedUser->update([
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'description' => $this->description,
            'image' => $this->image,
        ]);

        $this->resetInputs();
    }

    public function deleteUser()
    {
        $this->selectedUser->delete();
        $this->users = User::all();
        $this->resetInputs();
    }

    public function resetInputs()
    {
        $this->selectedUser = null;
        $this->firstname = '';
        $this->lastname = '';
        $this->email = '';
        $this->description = '';
        $this->image = '';
    }

    public function render()
    {
        return view('livewire.backend.users');
    }
}    

