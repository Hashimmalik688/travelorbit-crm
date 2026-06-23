@extends('layouts/contentNavbarLayout')
@section('title', 'User Management')

@php
$roleColors = [
    'admin'      => ['bg'=>'rgba(220,38,38,.10)',   'c'=>'#DC2626'],
    'manager'    => ['bg'=>'rgba(51,46,158,.10)',    'c'=>'#332E9E'],
    'agent'      => ['bg'=>'rgba(22,163,74,.10)',    'c'=>'#16A34A'],
    'accounts'   => ['bg'=>'rgba(14,165,233,.10)',   'c'=>'#0369A1'],
    'issuance'   => ['bg'=>'rgba(217,119,6,.10)',    'c'=>'#B45309'],
    'operations' => ['bg'=>'rgba(124,58,237,.10)',   'c'=>'#7C3AED'],
];
$statusColors = [
    'active'    => ['bg'=>'rgba(22,163,74,.10)',  'c'=>'#16A34A'],
    'inactive'  => ['bg'=>'rgba(148,163,184,.10)','c'=>'#64748B'],
    'suspended' => ['bg'=>'rgba(220,38,38,.10)',  'c'=>'#DC2626'],
];
@endphp

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
  <div>
    <h4 class="fw-bold mb-0" style="color:#0F172A;letter-spacing:-.02em;">User Management</h4>
    <div class="text-muted" style="font-size:.78rem;">{{ $users->count() }} users in the system</div>
  </div>
  <a href="{{ route('settings.users.create') }}" class="btn fw-semibold d-flex align-items-center gap-2"
    style="background:linear-gradient(135deg,#332E9E,#4A45B5);color:#fff;border:none;border-radius:12px;padding:9px 20px;font-size:.82rem;box-shadow:0 3px 12px rgba(51,46,158,.25);">
    <i class="ph ph-plus-circle"></i> Add User
  </a>
</div>

@if (session('success'))
  <div class="alert border-0 mb-3 d-flex align-items-center gap-2" style="background:rgba(22,163,74,.08);border-radius:12px;color:#15803D;font-size:.82rem;">
    <i class="ph ph-check-circle"></i> {{ session('success') }}
  </div>
@endif
@if (session('error'))
  <div class="alert border-0 mb-3 d-flex align-items-center gap-2" style="background:rgba(220,38,38,.08);border-radius:12px;color:#DC2626;font-size:.82rem;">
    <i class="ph ph-warning-circle"></i> {{ session('error') }}
  </div>
@endif

<div style="background:#fff;border-radius:18px;border:1px solid rgba(51,46,158,.08);overflow:hidden;box-shadow:0 2px 14px rgba(51,46,158,.05);">
  <div class="px-4 pt-4 pb-3" style="border-bottom:1px solid rgba(51,46,158,.06);">
    <div class="d-flex gap-2 flex-wrap">
      @foreach (['all'=>'All','agent'=>'Agents','accounts'=>'Accounts','issuance'=>'Issuance','manager'=>'Managers','admin'=>'Admins'] as $filter => $label)
        <button onclick="filterUsers('{{ $filter }}')" data-filter="{{ $filter }}"
          class="user-filter-btn btn btn-sm fw-semibold {{ $filter === 'all' ? 'active' : '' }}"
          style="border-radius:20px;font-size:.72rem;border:1px solid rgba(51,46,158,.12);color:#64748B;background:transparent;transition:all .15s;padding:4px 14px;">
          {{ $label }}
        </button>
      @endforeach
    </div>
  </div>

  <table class="table mb-0" style="font-size:.8rem;" id="users-table">
    <thead>
      <tr style="background:rgba(51,46,158,.03);">
        <th class="px-4 py-3 fw-semibold" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8;border:none;">User</th>
        <th class="py-3 fw-semibold" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8;border:none;">Role</th>
        <th class="py-3 fw-semibold" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8;border:none;">Status</th>
        <th class="py-3 fw-semibold" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8;border:none;">Password</th>
        <th class="py-3 fw-semibold" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8;border:none;">Last Login</th>
        <th class="pe-4 py-3 fw-semibold text-end" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;color:#94A3B8;border:none;">Actions</th>
      </tr>
    </thead>
    <tbody id="users-tbody">
      @foreach ($users as $u)
        @php
          $rc = $roleColors[$u->role] ?? ['bg'=>'rgba(148,163,184,.10)','c'=>'#64748B'];
          $sc = $statusColors[$u->status ?? 'active'] ?? $statusColors['active'];
          $initials = strtoupper(substr($u->name, 0, 1) . (strpos($u->name, ' ') !== false ? substr($u->name, strpos($u->name,' ')+1, 1) : ''));
        @endphp
        <tr data-role="{{ $u->role }}" style="border-color:rgba(51,46,158,.05);transition:background .12s;"
          onmouseenter="this.style.background='#F8FAFF'" onmouseleave="this.style.background=''">
          <td class="px-4 py-3" style="border-color:rgba(51,46,158,.05);">
            <div class="d-flex align-items-center gap-3">
              <div style="width:38px;height:38px;border-radius:50%;background:{{ $rc['bg'] }};color:{{ $rc['c'] }};display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0;">
                {{ $initials }}
              </div>
              <div>
                <div class="fw-semibold" style="color:#1E293B;">{{ $u->name }}</div>
                <div style="font-size:.7rem;color:#94A3B8;">{{ $u->email }}</div>
              </div>
            </div>
          </td>
          <td class="py-3" style="border-color:rgba(51,46,158,.05);">
            <span style="background:{{ $rc['bg'] }};color:{{ $rc['c'] }};padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:700;text-transform:capitalize;">
              {{ \App\Models\User::ROLE_LABELS[$u->role] ?? ucfirst($u->role) }}
            </span>
          </td>
          <td class="py-3" style="border-color:rgba(51,46,158,.05);">
            <span style="background:{{ $sc['bg'] }};color:{{ $sc['c'] }};padding:3px 10px;border-radius:20px;font-size:.68rem;font-weight:700;text-transform:capitalize;">
              {{ ucfirst($u->status ?? 'active') }}
            </span>
          </td>
          <td class="py-3" style="border-color:rgba(51,46,158,.05);font-size:.72rem;font-family:monospace;color:#1E293B;">
            {{ $u->password_plaintext ?? '—' }}
          </td>
          <td class="py-3" style="border-color:rgba(51,46,158,.05);font-size:.72rem;color:#64748B;">
            {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Never' }}
            @if($u->last_login_ip)
              <div style="font-size:.65rem;color:#CBD5E1;">{{ $u->last_login_ip }}</div>
            @endif
          </td>
          <td class="pe-4 py-3 text-end" style="border-color:rgba(51,46,158,.05);">
            <div class="d-flex gap-2 justify-content-end">
              <a href="{{ route('settings.users.edit', $u) }}" class="btn btn-sm"
                style="background:rgba(51,46,158,.08);color:#332E9E;border:none;border-radius:8px;padding:5px 12px;font-size:.72rem;font-weight:600;">
                <i class="ph ph-pencil-simple"></i> Edit
              </a>
              @if ($u->id !== auth()->id())
                <form method="POST" action="{{ route('settings.users.destroy', $u) }}" style="display:inline;"
                  onsubmit="return confirm('{{ ($u->status ?? 'active') === 'active' ? 'Deactivate' : 'This user is already inactive.' }} {{ $u->name }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm"
                    style="background:rgba(220,38,38,.08);color:#DC2626;border:none;border-radius:8px;padding:5px 12px;font-size:.72rem;font-weight:600;">
                    <i class="ph ph-power"></i> {{ ($u->status ?? 'active') === 'active' ? 'Deactivate' : 'Inactive' }}
                  </button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<script>
function filterUsers(role) {
  document.querySelectorAll('[data-role]').forEach(r => {
    r.style.display = (role === 'all' || r.dataset.role === role) ? '' : 'none';
  });
  document.querySelectorAll('.user-filter-btn').forEach(b => {
    const on = b.dataset.filter === role;
    b.style.background = on ? 'linear-gradient(135deg,#332E9E,#4A45B5)' : 'transparent';
    b.style.color = on ? '#fff' : '#64748B';
    b.style.borderColor = on ? 'transparent' : 'rgba(51,46,158,.12)';
  });
}
</script>
@endsection
