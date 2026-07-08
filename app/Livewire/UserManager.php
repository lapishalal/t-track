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
    public $editingUserId = null;

    public function mount()
    {
        abort_unless(auth()->user()?->isOwner(), 403);
    }

    public function createUser()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'role' => ['required', Rule::in(['operator', 'uploader'])],
        ];

        if ($this->editingUserId) {
            $rules['password'] = 'nullable|string|min:8';
        } else {
            $rules['password'] = 'required|string|min:8';
        }

        $this->validate($rules);

        $data = [
            'name' => trim($this->name),
            'email' => trim($this->email),
            'role' => $this->role,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update($data);
            session()->flash('success_users', "User {$user->name} berhasil diperbarui.");
        } else {
            User::create($data);
            session()->flash('success_users', "User {$this->name} berhasil dibuat.");
        }

        $this->resetForm();
    }

    public function editUser($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->isOwner()) {
            session()->flash('error_users', 'Tidak bisa mengedit user Owner.');
            return;
        }

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
    }

    public function cancelEdit()
    {
        $this->resetForm();
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

    private function resetForm()
    {
        $this->reset('name', 'email', 'password', 'editingUserId');
        $this->role = 'operator';
    }

    public function render()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('livewire.user-manager', ['users' => $users]);
    }
}
