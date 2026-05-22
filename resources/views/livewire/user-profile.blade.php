<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>My Profile</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; Profile
            </div>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Profile Photo --}}
        <div class="col-md-4 animate-in">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Photo</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if (Auth::user()->profile_photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" class="rounded-circle" width="120" height="120" style="object-fit: cover;">
                        @else
                            <div class="avatar avatar-xl mx-auto">
                                <span class="avatar-initial rounded-circle" style="width: 120px; height: 120px; font-size: 2.5rem; background: linear-gradient(135deg, #332E9E, #D83F87);">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <form wire:submit="uploadPhoto" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" class="form-control form-control-sm" wire:model="photo" accept="image/*">
                            @error('photo') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <div wire:loading wire:target="photo" class="text-primary small mt-1">Uploading...</div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            @if ($photo)
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ph ph-upload-simple me-1"></i> Upload
                                </button>
                            @endif
                            @if (Auth::user()->profile_photo_path)
                                <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removePhoto" wire:confirm="Remove profile photo?">
                                    <i class="ph ph-trash me-1"></i> Remove
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Profile Details --}}
        <div class="col-md-8 animate-in" style="animation-delay: 0.08s;">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Details</h5>
                </div>
                <div class="card-body">
                    <form wire:submit="updateProfile">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ph ph-check-circle me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card animate-in" style="animation-delay: 0.16s;">
                <div class="card-header">
                    <h5 class="card-title mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form wire:submit="updatePassword">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('currentPassword') is-invalid @enderror" wire:model="currentPassword">
                                @error('currentPassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('newPassword') is-invalid @enderror" wire:model="newPassword">
                                @error('newPassword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" wire:model="newPasswordConfirmation">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ph ph-lock-key me-1"></i> Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
