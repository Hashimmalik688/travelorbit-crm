@extends('layouts/contentNavbarLayout')
@section('title', 'Add User')

@section('content')
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="{{ route('settings.users.index') }}" style="color:#94A3B8;font-size:.85rem;"><i class="ph ph-arrow-left"></i></a>
  <h4 class="fw-bold mb-0" style="color:#0F172A;letter-spacing:-.02em;">Add New User</h4>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div style="background:#fff;border-radius:18px;border:1px solid rgba(51,46,158,.08);padding:28px;box-shadow:0 2px 14px rgba(51,46,158,.05);">
      <form method="POST" action="{{ route('settings.users.store') }}">
        @csrf

        {{-- Name + Email --}}
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:.72rem;color:#5A6080;">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}"
              class="form-control @error('name') is-invalid @enderror" style="border-radius:10px;" placeholder="John Smith">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:.72rem;color:#5A6080;">Email Address <span class="text-danger">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}"
              class="form-control @error('email') is-invalid @enderror" style="border-radius:10px;" placeholder="john@travelorbit.co.uk">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        {{-- Role --}}
        <div class="mb-3">
          <label class="form-label fw-semibold" style="font-size:.72rem;color:#5A6080;">Role <span class="text-danger">*</span></label>
          <div class="row g-2">
            @php
              $roles = ['agent'=>['Agent','#16A34A','Create and manage bookings'],'accounts'=>['Accounts Manager','#0EA5E9','Charge payments and invoice'],'issuance'=>['Issuance Manager','#D97706','Manage ticket issuance queue'],'manager'=>['Manager','#332E9E','Full access, no admin'],'admin'=>['Admin / CEO','#DC2626','Full system access + user management'],'operations'=>['Operations','#7C3AED','Operational support']];
            @endphp
            @foreach ($roles as $val => [$label, $color, $desc])
              <div class="col-md-4">
                <label style="cursor:pointer;display:block;">
                  <input type="radio" name="role" value="{{ $val }}" {{ old('role') === $val ? 'checked' : '' }} style="display:none;" class="role-radio">
                  <div class="role-card p-3" style="border-radius:12px;border:2px solid rgba(51,46,158,.10);transition:all .15s;">
                    <div class="fw-semibold" style="font-size:.78rem;color:{{ $color }};">{{ $label }}</div>
                    <div style="font-size:.65rem;color:#94A3B8;margin-top:2px;">{{ $desc }}</div>
                  </div>
                </label>
              </div>
            @endforeach
          </div>
          @error('role')<div class="text-danger mt-1" style="font-size:.72rem;">{{ $message }}</div>@enderror
        </div>

        {{-- Password --}}
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:.72rem;color:#5A6080;">Password <span class="text-danger">*</span></label>
            <input type="password" name="password"
              class="form-control @error('password') is-invalid @enderror" style="border-radius:10px;" placeholder="Min 8 characters">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" style="font-size:.72rem;color:#5A6080;">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" style="border-radius:10px;" placeholder="Repeat password">
          </div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn fw-semibold"
            style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border:none;border-radius:11px;padding:9px 22px;font-size:.82rem;">
            Create User
          </button>
          <a href="{{ route('settings.users.index') }}" class="btn fw-semibold"
            style="background:rgba(51,46,158,.07);color:#332E9E;border:none;border-radius:11px;padding:9px 18px;font-size:.82rem;">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>

  <div class="col-lg-5">
    <div style="background:rgba(51,46,158,.03);border-radius:16px;border:1px solid rgba(51,46,158,.08);padding:22px;">
      <h6 class="fw-bold mb-3" style="font-size:.82rem;color:#0F172A;">Role Permissions Guide</h6>
      @php $guide = ['Agent'=>['Create bookings','Add passengers, PNR, hotel','Edit until issuance queue'],'Accounts Manager'=>['Charge & decline payments','Invoice Ticket-in-Process bookings','View all bookings'],'Issuance Manager'=>['Manage issuance queue','Mark tickets in process','Restore bookings to pending'],'Manager'=>['Everything (except user management)','View all reports and dashboards'],'Admin / CEO'=>['Everything + user management','View all stats across all agents','Full system control'],'Operations'=>['Create and support bookings','Same as agent level']]; @endphp
      @foreach ($guide as $role => $perms)
        <div class="mb-3">
          <div class="fw-semibold" style="font-size:.73rem;color:#374151;">{{ $role }}</div>
          <ul class="mb-0 ps-3" style="font-size:.68rem;color:#64748B;">
            @foreach ($perms as $p)<li>{{ $p }}</li>@endforeach
          </ul>
        </div>
      @endforeach
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.role-radio').forEach(r => {
  r.addEventListener('change', function() {
    document.querySelectorAll('.role-card').forEach(c => {
      c.style.borderColor = 'rgba(51,46,158,.10)';
      c.style.background = '';
    });
    if (this.checked) {
      this.parentElement.querySelector('.role-card').style.borderColor = '#332E9E';
      this.parentElement.querySelector('.role-card').style.background = 'rgba(51,46,158,.05)';
    }
  });
  if (r.checked) {
    r.parentElement.querySelector('.role-card').style.borderColor = '#332E9E';
    r.parentElement.querySelector('.role-card').style.background = 'rgba(51,46,158,.05)';
  }
});
</script>
@endsection
