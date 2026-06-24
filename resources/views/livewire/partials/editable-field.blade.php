{{-- Editable inline field — editing controlled by parent section's Alpine state --}}
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
  $editingExp = $editingVar ? '$parent.' . $editingVar : 'false';
  $fieldId = str_replace(['.','[',']'], ['-','',''], $model);
@endphp

<div class="bv-field-row">
  <div style="flex:1;min-width:0;">
    @if($label)<div class="bv-label">{{ $label }}</div>@endif
    <div style="position:relative;{{ $locked ? 'opacity:.45;' : '' }}">
      {{-- Read-only display --}}
      <div x-show="!{{ $editingExp }}" style="min-height:28px;display:flex;align-items:center;gap:6px;{{ $locked ? 'pointer-events:none;' : '' }}">
        <span class="bv-value" style="word-break:break-word;{{ empty($val) ? 'color:#C4C9D4;' : '' }}">{{ $val ?: ($placeholder ?: '-') }}</span>
        @if($locked)
          <span class="bv-edit-pencil locked" title="Read only"><i class="ph ph-lock-simple"></i></span>
        @endif
      </div>
      {{-- Inline edit (only when editingVar is set and not locked) --}}
      @if($editingVar && !$locked)
      <div x-show="{{ $editingExp }}" x-cloak style="display:flex;align-items:flex-start;gap:4px;">
        @if($type === 'select')
          <div style="flex:1;min-width:0;">
            <x-styled-select-sm :modelName="$model" :options="$options" :placeholder="$placeholder ?: ''" :live="true" />
          </div>
        @elseif($type === 'textarea')
          <textarea wire:model="{{ $model }}" class="bv-input-inline bv-input-sm" rows="2" placeholder="{{ $placeholder }}"></textarea>
        @elseif($type === 'date')
          <div style="flex:1;min-width:0;"><x-date-picker :modelName="$model" :compact="true" /></div>
        @elseif($type === 'datetime-local')
          <div style="flex:1;min-width:0;"><x-date-picker :modelName="$model" :compact="true" :isDateTime="true" /></div>
        @elseif($type === 'email')
          <input type="email" wire:model="{{ $model }}" class="bv-input-inline bv-input-sm" placeholder="{{ $placeholder }}" style="font-size:.72rem;">
        @elseif($type === 'cost')
          <input type="number" wire:model.blur="{{ $rawModel }}" step="0.01" min="0" class="bv-input-inline bv-input-sm" placeholder="{{ $placeholder }}" style="font-size:.72rem;width:100px;">
        @elseif($type === 'rooms')
          <input type="number" wire:model.live="{{ $rawModel }}" min="1" class="bv-input-inline bv-input-sm" placeholder="{{ $placeholder }}" style="font-size:.72rem;width:70px;">
        @else
          <input type="text" wire:model="{{ $model }}" class="bv-input-inline bv-input-sm" placeholder="{{ $placeholder }}" style="font-size:.72rem;">
        @endif
      </div>
      @endif
    </div>
  </div>
</div>
