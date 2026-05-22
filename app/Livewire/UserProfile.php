<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserProfile extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $currentPassword = '';
    public $newPassword = '';
    public $newPasswordConfirmation = '';
    public $photo;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'photo' => 'nullable|image|max:2048',
            'currentPassword' => 'nullable|current_password',
            'newPassword' => 'nullable|min:8|same:newPasswordConfirmation',
        ];
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        Auth::user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('success', 'Profile updated successfully.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => 'required|current_password',
            'newPassword' => 'required|min:8|same:newPasswordConfirmation',
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->currentPassword = '';
        $this->newPassword = '';
        $this->newPasswordConfirmation = '';

        session()->flash('success', 'Password changed successfully.');
    }

    public function uploadPhoto(): void
    {
        $this->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $user = Auth::user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $this->photo->store('avatars', 'public');
        $user->update(['profile_photo_path' => $path]);

        $this->photo = null;
        session()->flash('success', 'Profile photo updated.');
    }

    public function removePhoto(): void
    {
        $user = Auth::user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        session()->flash('success', 'Profile photo removed.');
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}
