<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $name = '';
    public $email = '';
    public $password = '';
    public $role = 'agent';
    public $is_active = true;
    public $editingUserId = null;
    public $showModal = false;

    public function mount()
    {
        abort_if(Auth::user()->role !== 'admin', 403, 'Only administrators can access this page.');
    }

    protected function rules(): array
    {
        $uniqueRule = $this->editingUserId
            ? 'unique:users,email,' . $this->editingUserId
            : 'unique:users,email';

        $passwordRule = $this->editingUserId ? 'nullable|min:8' : 'required|min:8';

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|' . $uniqueRule,
            'password' => $passwordRule,
            'role' => 'required|in:admin,manager,agent,accounting,operations',
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->reset(['name', 'email', 'password', 'role', 'is_active', 'editingUserId']);
        $this->role = 'agent';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        $this->is_active = (bool) $user->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUserId) {
            User::find($this->editingUserId)->update($data);
            session()->flash('success', 'User updated successfully.');
        } else {
            User::create($data);
            session()->flash('success', 'User created successfully.');
        }

        $this->showModal = false;
        $this->reset(['name', 'email', 'password', 'role', 'is_active', 'editingUserId']);
    }

    public function deactivate($userId): void
    {
        User::find($userId)->update(['is_active' => false]);
        session()->flash('success', 'User deactivated.');
    }

    public function activate($userId): void
    {
        User::find($userId)->update(['is_active' => true]);
        session()->flash('success', 'User activated.');
    }

    public function render()
    {
        $users = User::query()
            ->withCount('bookings')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.user-management', [
            'users' => $users,
        ]);
    }
}
