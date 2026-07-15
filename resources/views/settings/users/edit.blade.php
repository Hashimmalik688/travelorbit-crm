@extends('layouts/contentNavbarLayout')
@section('title', 'Edit User - '.$user->name)

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="{{ route('settings.users.index') }}" style="color:#475569;font-size:1.02rem;"><i class="ph ph-arrow-left"></i></a>
  <h4 class="fw-bold mb-0" style="color:#0F172A;letter-spacing:-.02em;">Edit User</h4>
</div>

@if (session('success'))
  <div class="alert border-0 mb-3" style="background:rgba(22,163,74,.08);border-radius:12px;color:#15803D;font-size:0.984rem;">
    <i class="ph ph-check-circle me-2"></i>{{ session('success') }}
  </div>
@endif

<form method="POST" action="{{ route('settings.users.update', $user) }}" enctype="multipart/form-data">
  @csrf @method('PUT')
<div class="row g-3">
  <div class="col-lg-7">
    <div style="background:#fff;border-radius:18px;border:1px solid rgba(51,46,158,.08);padding:28px;box-shadow:0 2px 14px rgba(51,46,158,.05);">

        {{-- Profile Photo --}}
        <div class="d-flex align-items-center gap-3 mb-4">
          <div id="avatar-preview" style="width:64px;height:64px;border-radius:16px;background:rgba(51,46,158,.06);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
            @if ($user->profile_photo_path)
              <img src="{{ asset('storage/'.$user->profile_photo_path) }}" style="width:100%;height:100%;object-fit:cover;">
            @else
              <i class="ph ph-user" style="font-size:1.7rem;color:#332E9E;"></i>
            @endif
          </div>
          <div class="flex-grow-1">
            <label class="form-label fw-semibold" style="font-size:0.864rem;color:#5A6080;">Profile Photo</label>
            <input type="file" name="profile_photo" accept="image/*" onchange="previewAvatar(this)"
              class="form-control form-control-sm @error('profile_photo') is-invalid @enderror" style="border-radius:10px;">
            @error('profile_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="d-flex align-items-center justify-content-between mt-1" style="gap:8px;">
              <span class="text-muted" style="font-size:0.78rem;">JPG, PNG or WebP · max 2 MB</span>
              @if ($user->profile_photo_path)
                <label class="d-flex align-items-center gap-1 mb-0" style="font-size:0.816rem;color:#DC2626;cursor:pointer;">
                  <input type="checkbox" name="remove_photo" value="1"> Remove photo
                </label>
              @endif
            </div>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:0.864rem;color:#5A6080;">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}"
              class="form-control @error('name') is-invalid @enderror" style="border-radius:10px;">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:0.864rem;color:#5A6080;">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}"
              class="form-control @error('email') is-invalid @enderror" style="border-radius:10px;">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Role --}}
        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:0.864rem;color:#5A6080;">Role <span class="text-danger">*</span></label>
          <div class="row g-2">
            @php $roles = ['agent'=>['Agent','#16A34A'],'accounts'=>['Accounts Manager','#0EA5E9'],'issuance'=>['Issuance Manager','#D97706'],'manager'=>['Manager','#332E9E'],'admin'=>['Admin / CEO','#DC2626'],'operations'=>['Operations','#7C3AED']]; @endphp
            @foreach ($roles as $val => [$label, $color])
              <div class="col-md-4">
                <label style="cursor:pointer;display:block;">
                  <input type="radio" name="role" value="{{ $val }}" {{ old('role', $user->role) === $val ? 'checked' : '' }} style="display:none;" class="role-radio">
                  <div class="role-card p-2 text-center" style="border-radius:10px;border:2px solid rgba(51,46,158,.10);transition:all .15s;font-size:0.9rem;font-weight:600;color:{{ $color }};">
                    {{ $label }}
                  </div>
                </label>
              </div>
            @endforeach
          </div>
          @error('role')<div class="text-danger mt-1" style="font-size:0.864rem;">{{ $message }}</div>@enderror
        </div>

        {{-- Status --}}
        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:0.864rem;color:#5A6080;">Status</label>
          <div class="d-flex gap-2">
            @foreach (['active'=>['Active','#16A34A'],'inactive'=>['Inactive','#64748B'],'suspended'=>['Suspended','#DC2626']] as $s => [$sl, $sc])
              <label style="cursor:pointer;display:flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;border:1.5px solid {{ old('status',$user->status??'active') === $s ? $sc : 'rgba(51,46,158,.12)' }};background:{{ old('status',$user->status??'active') === $s ? 'rgba(0,0,0,.03)' : 'transparent' }};font-size:0.9rem;font-weight:600;color:{{ old('status',$user->status??'active') === $s ? $sc : '#64748B' }};">
                <input type="radio" name="status" value="{{ $s }}" {{ old('status',$user->status??'active') === $s ? 'checked' : '' }} style="display:none;">
                {{ $sl }}
              </label>
            @endforeach
          </div>
        </div>

        {{-- Reset password --}}
        <div class="p-3 mb-4" style="background:rgba(51,46,158,.03);border-radius:12px;border:1px solid rgba(51,46,158,.08);">
          <div class="fw-semibold mb-2" style="font-size:0.9rem;color:#374151;">Reset Password <span class="text-muted fw-normal">(leave blank to keep current)</span></div>
          <div class="row g-2">
            <div class="col-md-6">
              <input type="password" name="password" class="form-control form-control-sm @error('password') is-invalid @enderror" style="border-radius:8px;" placeholder="New password">
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <input type="password" name="password_confirmation" class="form-control form-control-sm" style="border-radius:8px;" placeholder="Confirm new password">
            </div>
          </div>
        </div>

        {{-- Plain text password --}}
        <div class="p-3 mb-4" style="background:rgba(255,107,53,.04);border-radius:12px;border:1px solid rgba(255,107,53,.15);">
          <div class="fw-semibold mb-1" style="font-size:0.9rem;color:#374151;">Stored Password <span class="text-muted fw-normal">(plain text — editable)</span></div>
          <input type="text" name="password_plaintext" value="{{ old('password_plaintext', $user->password_plaintext) }}"
                 class="form-control form-control-sm" style="border-radius:8px;font-family:monospace;" placeholder="Enter plain text password">
          <div class="text-muted" style="font-size:0.78rem;margin-top:3px;">
            Changes here sync to the hashed password on save.
          </div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn fw-semibold"
            style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border:none;border-radius:11px;padding:9px 22px;font-size:0.984rem;">
            Save Changes
          </button>
          <a href="{{ route('settings.users.index') }}" class="btn fw-semibold"
            style="background:rgba(51,46,158,.07);color:#332E9E;border:none;border-radius:11px;padding:9px 18px;font-size:0.984rem;">Cancel</a>
        </div>
    </div>
  </div>

  <div class="col-lg-5">
    {{-- Account Info --}}
    <div style="background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);overflow:hidden;margin-bottom:16px;">
      <div class="px-4 py-3" style="border-bottom:1px solid rgba(51,46,158,.06);">
        <div class="fw-bold" style="font-size:0.984rem;color:#0F172A;">Account Info</div>
      </div>
      <div class="px-4 py-3">
        <div class="d-flex justify-content-between mb-2" style="font-size:0.9rem;">
          <span class="text-muted">Joined</span>
          <span class="fw-semibold">{{ $user->created_at->format('d M Y') }}</span>
        </div>
        <div class="d-flex justify-content-between mb-2" style="font-size:0.9rem;">
          <span class="text-muted">Last Login</span>
          <span class="fw-semibold">{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Never' }}</span>
        </div>
        @if ($user->last_login_ip)
          <div class="d-flex justify-content-between" style="font-size:0.9rem;">
            <span class="text-muted">Last IP</span>
            <span class="fw-semibold" style="font-family:monospace;">{{ $user->last_login_ip }}</span>
          </div>
        @endif
      </div>
    </div>

    {{-- Permissions --}}
    <div style="background:#fff;border-radius:16px;border:1px solid rgba(51,46,158,.08);padding:22px;box-shadow:0 2px 14px rgba(51,46,158,.05);">
      @include('settings.users._permissions', ['currentPermissions' => old('permissions', $user->permissions ?? [])])
    </div>
  </div>
</div>
</form>

<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('avatar-preview').innerHTML =
        '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
document.querySelectorAll('.role-radio').forEach(r => {
  r.addEventListener('change', function() {
    document.querySelectorAll('.role-card').forEach(c => { c.style.borderColor='rgba(51,46,158,.10)';c.style.background=''; });
    if(this.checked){this.parentElement.querySelector('.role-card').style.borderColor='#332E9E';this.parentElement.querySelector('.role-card').style.background='rgba(51,46,158,.05)';}
  });
  if(r.checked){r.parentElement.querySelector('.role-card').style.borderColor='#332E9E';r.parentElement.querySelector('.role-card').style.background='rgba(51,46,158,.05)';}
});
</script>
@endsection
