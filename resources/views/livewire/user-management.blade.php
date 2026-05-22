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
                        <th class="text-center">Bookings</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleMap = ['admin' => 'danger', 'manager' => 'warning', 'accounting' => 'info', 'operations' => 'dark'];
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
                            <td class="text-center">{{ $user->bookings_count }}</td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
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
                            <td colspan="7">
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
                        <div class="mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" wire:model="role">
                                <option value="agent">Agent</option>
                                <option value="operations">Operations</option>
                                <option value="accounting">Accounting</option>
                                <option value="manager">Manager</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
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
