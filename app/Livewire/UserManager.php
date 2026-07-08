<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManager extends Component
{
    public $name;
    public $email;
    public $password;
    public $role = 'operator';

    public function mount()
    {
        abort_unless(auth()->user()?->isOwner(), 403);
    }

    public function createUser()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['operator', 'uploader'])],
        ]);

        User::create([
            'name' => trim($this->name),
            'email' => trim($this->email),
            'password' => Hash::make($this->password),
            'role' => $this->role,
        ]);

        session()->flash('success_users', "User {$this->name} berhasil dibuat.");

        $this->reset('name', 'email', 'password', 'role');
        $this->reset('role');
    }

    public function deleteUser($userId)
    {
        if ($userId == auth()->id()) {
            session()->flash('error_users', 'Tidak bisa menghapus akun sendiri.');
            return;
        }

        $user = User::findOrFail($userId);

        if ($user->isOwner()) {
            session()->flash('error_users', 'Tidak bisa menghapus user Owner.');
            return;
        }

        $user->delete();
        session()->flash('success_users', "User {$user->name} berhasil dihapus.");
    }

    public function render()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('livewire.user-manager', ['users' => $users]);
    }
}
