<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>User Management</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="#">Settings</a> &rsaquo; Users
            </div>
        </div>
        <div class="to-page-header-right">
            <button class="btn btn-orange btn-sm" wire:click="create">
                <i class="ph ph-user-plus me-1"></i> Create User
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Password</th>
                        <th class="text-center">Bookings</th>
                        <th>Created</th>
                        <th>Last Login</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($user->profile_photo_path)
                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                             alt="{{ $user->name }}" class="rounded-circle"
                                             style="width:32px;height:32px;object-fit:cover;">
                                    @else
                                        <span class="avatar-initial rounded-circle"
                                              style="display:inline-flex;align-items:center;justify-content:center;
                                                     width:32px;height:32px;background:rgba(51,46,158,.08);
                                                     font-size:.72rem;font-weight:700;color:#332E9E;">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </span>
                                    @endif
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleMap = ['admin' => 'danger', 'manager' => 'warning', 'accounts' => 'info', 'issuance' => 'warning', 'operations' => 'dark'];
                                @endphp
                                <span class="badge bg-label-{{ $roleMap[$user->role] ?? 'primary' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-label-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td><code style="font-size:.72rem;">{{ $user->password_plaintext ?? '—' }}</code></td>
                            <td class="text-center">{{ $user->bookings_count }}</td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                @if ($user->last_login_at)
                                    <span title="{{ $user->last_login_at->format('d M Y H:i') }}">
                                        {{ $user->last_login_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-icon btn-outline-primary" wire:click="edit({{ $user->id }})" title="Edit">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                    @if ($user->id !== Auth::id())
                                        @if ($user->is_active)
                                            <button class="btn btn-sm btn-icon btn-outline-danger" wire:click="deactivate({{ $user->id }})" wire:confirm="Deactivate this user?" title="Deactivate">
                                                <i class="ph ph-user-x"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-icon btn-outline-success" wire:click="activate({{ $user->id }})" title="Activate">
                                                <i class="ph ph-user-check"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-users-three"></i></div>
                                    <h5>No users found</h5>
                                    <p>Create your first user to get started.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingUserId ? 'Edit User' : 'Create User' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="Enter name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" placeholder="Enter email">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                Password
                                @if ($editingUserId) <span class="text-muted">(leave blank to keep)</span>
                                @else <span class="text-danger">*</span> @endif
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password" placeholder="Enter password">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        @if ($editingUserId)
                        <div class="mb-3">
                            <label class="form-label">Stored Password (plain text)</label>
                            <input type="text" class="form-control" wire:model="passwordPlaintext"
                                   style="font-family:monospace;" placeholder="Enter plain text password">
                            <div class="text-muted" style="font-size:.68rem;margin-top:3px;">
                                Editable — changes here sync to the hashed password on save.
                            </div>
                        </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <x-styled-select modelName="role" :options="[['value'=>'agent','label'=>'Agent'],['value'=>'operations','label'=>'Operations'],['value'=>'accounts','label'=>'Accounts'],['value'=>'issuance','label'=>'Issuance'],['value'=>'manager','label'=>'Manager'],['value'=>'admin','label'=>'Admin']]" placeholder="Select Role" />
                            @error('role') <div class="text-danger" style="font-size:.78rem;margin-top:4px;">{{ $message }}</div> @enderror
                        </div>
                        @if ($editingUserId)
                        <div class="mb-3">
                            <label class="form-label">Profile Photo</label>
                            <div class="d-flex align-items-center gap-3">
                                @if ($editingUserPhotoPath)
                                    <img src="{{ asset('storage/' . $editingUserPhotoPath) }}"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:10px;">
                                @else
                                    <div style="width:48px;height:48px;border-radius:10px;background:rgba(51,46,158,.08);
                                         display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.75rem;color:#332E9E;">
                                        {{ strtoupper(substr($name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <input type="file" wire:model="photo" accept="image/*"
                                           class="form-control form-control-sm @error('photo') is-invalid @enderror"
                                           style="font-size:.75rem;">
                                    @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if ($editingUserPhotoPath)
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-1"
                                                wire:click="removePhoto" style="font-size:.7rem;">
                                            <i class="ph ph-trash me-1"></i> Remove
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" wire:model="is_active">
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn btn-orange" wire:click="save">
                            <i class="ph ph-check-circle me-1"></i> {{ $editingUserId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
