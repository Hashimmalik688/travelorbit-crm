{{-- Editable inline field — editing controlled by parent section's Alpine state OR a PHP boolean --}}
@php
  $locked = $locked ?? false;
  $type = $type ?? 'text';
  $model = $model ?? '';
  $label = $label ?? '';
  $val = $val ?? '';
  $placeholder = $placeholder ?? '';
  $options = $options ?? [];
  $rawModel = $rawModel ?? $model;
  $editingVar = $editingVar ?? null;
  $phpEditing = isset($phpEditing) ? (bool)$phpEditing : null;
  // PHP-controlled takes priority; Alpine-controlled is the fallback
  $usePhp = $phpEditing !== null;
  $editingExp = $editingVar ?? 'false';
  $fieldId = str_replace(['.','[',']'], ['-','',''], $model);
@endphp

<div class="bv-field-row">
  <div style="flex:1;min-width:0;">
    @if($label)<div class="bv-label">{{ $label }}</div>@endif
    <div style="position:relative;{{ $locked ? 'opacity:.45;' : '' }}">
      {{-- Read-only display --}}
      @if($usePhp)
        @if(!$phpEditing)
        <div style="min-height:28px;display:flex;align-items:center;gap:6px;{{ $locked ? 'pointer-events:none;' : '' }}">
          <span class="bv-value" style="word-break:break-word;{{ empty($val) ? 'color:#475569;' : '' }}">{{ $val ?: ($placeholder ?: '-') }}</span>
          @if($locked)<span class="bv-edit-pencil locked" title="Read only"><i class="ph ph-lock-simple"></i></span>@endif
        </div>
        @endif
      @else
        <div x-show="!{{ $editingExp }}" style="min-height:28px;display:flex;align-items:center;gap:6px;{{ $locked ? 'pointer-events:none;' : '' }}">
          <span class="bv-value" style="word-break:break-word;{{ empty($val) ? 'color:#475569;' : '' }}">{{ $val ?: ($placeholder ?: '-') }}</span>
          @if($locked)<span class="bv-edit-pencil locked" title="Read only"><i class="ph ph-lock-simple"></i></span>@endif
        </div>
      @endif
      {{-- Inline edit --}}
      @if(!$locked)
        @if($usePhp)
          @if($phpEditing)
          <div style="display:flex;align-items:flex-start;gap:4px;">
            @include('livewire.partials._editable-field-input')
          </div>
          @endif
        @elseif($editingVar)
          <div x-show="{{ $editingExp }}" x-cloak style="display:flex;align-items:flex-start;gap:4px;">
            @include('livewire.partials._editable-field-input')
          </div>
        @endif
      @endif
    </div>
  </div>
</div>
