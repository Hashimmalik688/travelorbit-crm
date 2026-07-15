{{-- Permission checkbox matrix. Shared by create + edit.
     Expects $currentPermissions (array of keys already granted). --}}
@php
  $registry   = config('permissions.permissions');
  $groupOrder = config('permissions.groups');
  $allKeys    = array_keys($registry);

  // Presets for the role auto-fill JS ('*' => every key).
  $presetsJs = [];
  foreach (config('permissions.presets') as $r => $keys) {
      $presetsJs[$r] = ($keys === ['*']) ? $allKeys : $keys;
  }

  // Group the registry for display.
  $grouped = [];
  foreach ($registry as $key => $meta) {
      $grouped[$meta['group']][$key] = $meta;
  }

  $current = $currentPermissions ?? [];
@endphp

<style>
  .perm-check{display:inline-flex;align-items:center;gap:6px;cursor:pointer;padding:6px 11px;border-radius:9px;
    border:1.5px solid rgba(51,46,158,.12);background:#fff;font-size:0.864rem;font-weight:600;color:#475569;transition:all .12s;user-select:none;}
  .perm-check i{font-size:1.02rem;}
  .perm-check:hover{border-color:rgba(51,46,158,.3);}
  .perm-check.on{border-color:#332E9E;background:rgba(51,46,158,.06);color:#332E9E;}
  .perm-check input{display:none;}
  .perm-group{padding:10px 12px;border-radius:12px;background:rgba(51,46,158,.02);border:1px solid rgba(51,46,158,.06);}
</style>

<div class="mb-3" id="perm-matrix-wrap">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <label class="form-label fw-semibold mb-0" style="font-size:0.864rem;color:#5A6080;">Permissions <span class="text-danger">*</span></label>
    <span class="text-muted" style="font-size:0.78rem;">The role sets a starting point — tick to customise.</span>
  </div>

  <div id="perm-admin-note" style="display:none;background:rgba(220,38,38,.05);border:1px solid rgba(220,38,38,.15);border-radius:10px;padding:9px 12px;font-size:0.816rem;color:#B91C1C;margin-bottom:10px;">
    <i class="ph ph-shield-check me-1"></i> Admins have full access to everything automatically — permissions are managed for you.
  </div>

  <div id="perm-groups" class="d-flex flex-column gap-2">
    @foreach ($groupOrder as $group)
      @if (!empty($grouped[$group]))
        <div class="perm-group">
          <div class="fw-semibold mb-2" style="font-size:0.744rem;letter-spacing:.08em;text-transform:uppercase;color:#475569;">{{ $group }}</div>
          <div class="d-flex flex-wrap gap-2">
            @foreach ($grouped[$group] as $key => $meta)
              <label class="perm-check {{ in_array($key, $current, true) ? 'on' : '' }}" title="{{ $meta['description'] }}">
                <input type="checkbox" name="permissions[]" value="{{ $key }}" class="perm-box"
                  {{ in_array($key, $current, true) ? 'checked' : '' }}>
                <i class="{{ $meta['icon'] }}"></i> {{ $meta['label'] }}
              </label>
            @endforeach
          </div>
        </div>
      @endif
    @endforeach
  </div>
  @error('permissions')<div class="text-danger mt-1" style="font-size:0.864rem;">{{ $message }}</div>@enderror
  @error('permissions.*')<div class="text-danger mt-1" style="font-size:0.864rem;">Invalid permission selected.</div>@enderror
</div>

<script>
(function(){
  const PRESETS   = @json($presetsJs);
  const wrap      = document.getElementById('perm-matrix-wrap');
  const adminNote = document.getElementById('perm-admin-note');
  const groupsEl  = document.getElementById('perm-groups');
  const boxes     = () => wrap.querySelectorAll('.perm-box');

  function style(b){ b.closest('.perm-check').classList.toggle('on', b.checked); }

  function applyPreset(role){
    if(role === 'admin'){
      boxes().forEach(b => { b.checked = true; b.disabled = true; style(b); });
      adminNote.style.display = '';
      groupsEl.style.opacity  = '.5';
      return;
    }
    adminNote.style.display = 'none';
    groupsEl.style.opacity  = '';
    const preset = PRESETS[role] || [];
    boxes().forEach(b => { b.disabled = false; b.checked = preset.includes(b.value); style(b); });
  }

  // Selecting a role fills its preset (starting point the admin can then tweak).
  document.querySelectorAll('.role-radio').forEach(r => {
    r.addEventListener('change', function(){ if(this.checked) applyPreset(this.value); });
  });
  boxes().forEach(b => b.addEventListener('change', () => style(b)));

  // On load: lock the matrix if admin is already selected, otherwise just paint current state.
  const selected = document.querySelector('.role-radio:checked');
  if (selected && selected.value === 'admin') applyPreset('admin');
  else boxes().forEach(b => style(b));
})();
</script>
