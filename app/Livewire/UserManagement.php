<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithFileUploads, WithPagination;

    public $name = '';
    public $email = '';
    public $password = '';
    public $passwordPlaintext = '';
    public $role = 'agent';
    public $is_active = true;
    public $editingUserId = null;
    public $editingUserPhotoPath = null;
    public $photo = null;
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
            'role' => 'required|in:admin,manager,agent,accounts,issuance,operations',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|max:2048',
        ];
    }

    public function create(): void
    {
        $this->reset(['name', 'email', 'password', 'passwordPlaintext', 'role', 'is_active', 'editingUserId', 'editingUserPhotoPath', 'photo']);
        $this->role = 'agent';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->editingUserPhotoPath = $user->profile_photo_path;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->passwordPlaintext = $user->password_plaintext ?? '';
        $this->role = $user->role;
        $this->is_active = (bool) $user->is_active;
        $this->photo = null;
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
            $data['password_plaintext'] = $this->password;
        } elseif ($this->editingUserId && $this->passwordPlaintext) {
            $data['password'] = Hash::make($this->passwordPlaintext);
            $data['password_plaintext'] = $this->passwordPlaintext;
        }

        if ($this->editingUserId) {
            $user = User::find($this->editingUserId);

            if ($this->photo) {
                if ($user->profile_photo_path) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }
                $data['profile_photo_path'] = $this->photo->store('avatars', 'public');
            }

            $user->update($data);
            session()->flash('success', 'User updated successfully.');
        } else {
            User::create($data);
            session()->flash('success', 'User created successfully.');
        }

        $this->showModal = false;
        $this->reset(['name', 'email', 'password', 'passwordPlaintext', 'role', 'is_active', 'editingUserId', 'editingUserPhotoPath', 'photo']);
        $this->role = 'agent';
        $this->is_active = true;
    }

    public function removePhoto(): void
    {
        if (!$this->editingUserId) return;

        $user = User::find($this->editingUserId);
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        $this->editingUserPhotoPath = null;
        $this->photo = null;
        session()->flash('success', 'Profile photo removed.');
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
