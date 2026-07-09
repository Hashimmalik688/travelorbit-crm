<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('settings.users.index', compact('users'));
    }

    public function create()
    {
        return view('settings.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = [
            'name'               => $request->name,
            'email'              => strtolower($request->email),
            'password'           => $request->password,
            'password_plaintext' => $request->password,
            'role'               => $request->role,
            'permissions'        => $this->resolvePermissions($request),
            'is_active'          => $request->boolean('is_active', true),
            'status'             => 'active',
        ];

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo_path'] = $request->file('profile_photo')->store('avatars', 'public');
        }

        $user = User::create($data);

        AuditLog::logAction(
            action:      'user_created',
            user:        auth()->user(),
            model:       'User',
            model_id:    $user->id,
            description: "Created user {$user->email} with role {$user->role}",
        );

        return redirect()->route('settings.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    public function show(User $user)
    {
        $logs = AuditLog::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere(function ($q2) use ($user) {
                  $q2->where('model', 'User')->where('model_id', $user->id);
              });
        })->latest()->take(30)->get();

        return view('settings.users.show', compact('user', 'logs'));
    }

    public function edit(User $user)
    {
        return view('settings.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $old = ['role' => $user->role, 'status' => $user->status, 'permissions' => $user->permissions];

        $attributes = [
            'name'   => $request->name,
            'email'  => strtolower($request->email),
            'role'   => $request->role,
            'permissions' => $this->resolvePermissions($request),
            'status' => $request->input('status', 'active'),
            'is_active' => $request->input('status', 'active') === 'active',
        ];

        // Profile photo: replace with a new upload, or remove the current one.
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $attributes['profile_photo_path'] = $request->file('profile_photo')->store('avatars', 'public');
        } elseif ($request->boolean('remove_photo') && $user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $attributes['profile_photo_path'] = null;
        }

        $user->update($attributes);

        if ($request->filled('password')) {
            $user->update([
                'password'           => $request->password,
                'password_plaintext' => $request->password,
            ]);
        } elseif ($request->filled('password_plaintext')) {
            $user->update([
                'password'           => Hash::make($request->password_plaintext),
                'password_plaintext' => $request->password_plaintext,
            ]);
        }

        AuditLog::logAction(
            action:      'user_updated',
            user:        auth()->user(),
            model:       'User',
            model_id:    $user->id,
            description: "Updated user {$user->email}",
            changes:     ['before' => $old, 'after' => ['role' => $user->role, 'status' => $user->status, 'permissions' => $user->permissions]],
        );

        return redirect()->route('settings.users.index')
            ->with('success', "User {$user->name} updated.");
    }

    /**
     * Resolve the permission keys to persist. Admins get every key (they are a
     * hard-wired super-user regardless); everyone else keeps only posted keys
     * that exist in the registry.
     */
    private function resolvePermissions(Request $request): array
    {
        $valid = array_keys(config('permissions.permissions'));

        if ($request->input('role') === 'admin') {
            return $valid;
        }

        return array_values(array_intersect((array) $request->input('permissions', []), $valid));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        AuditLog::logAction(
            action:      'user_deactivated',
            user:        auth()->user(),
            model:       'User',
            model_id:    $user->id,
            description: "Deactivated user {$user->email}",
        );

        $user->update(['status' => 'inactive', 'is_active' => false]);

        return redirect()->route('settings.users.index')
            ->with('success', "User {$user->name} deactivated.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate(['password' => 'required|string|min:8|confirmed']);

        $user->update([
            'password'           => $request->password,
            'password_plaintext' => $request->password,
        ]);

        AuditLog::logAction(
            action:      'password_reset',
            user:        auth()->user(),
            model:       'User',
            model_id:    $user->id,
            description: "Admin reset password for {$user->email}",
        );

        return back()->with('success', "Password reset for {$user->name}.");
    }
}
