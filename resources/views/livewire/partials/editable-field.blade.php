{{-- Editable inline field with pencil toggle --}}
@php
  $locked = $locked ?? false;
  $type = $type ?? 'text';
  $model = $model ?? '';
  $label = $label ?? '';
  $val = $val ?? '';
  $placeholder = $placeholder ?? '';
  $options = $options ?? [];
  $rawModel = $rawModel ?? $model;
  $fieldId = str_replace(['.','[',']'], ['-','',''], $model);
@endphp

<div class="bv-field-row">
  <div style="flex:1;min-width:0;">
    @if($label)<div class="bv-label">{{ $label }}</div>@endif
    <div x-data="{ editing: false }" style="position:relative;{{ $locked ? 'opacity:.45;' : '' }}">
      {{-- Read-only display --}}
      <div x-show="!editing" style="min-height:28px;display:flex;align-items:center;gap:6px;{{ $locked ? 'pointer-events:none;' : '' }}">
        <span class="bv-value" style="word-break:break-word;{{ empty($val) ? 'color:#C4C9D4;' : '' }}">{{ $val ?: ($placeholder ?: '-') }}</span>
        @if(!$locked)
          <button type="button" @click="editing = true" class="bv-edit-pencil" title="Edit"><i class="ph ph-pencil-simple"></i></button>
        @else
          <span class="bv-edit-pencil locked" title="Read only"><i class="ph ph-lock-simple"></i></span>
        @endif
      </div>
      {{-- Inline edit --}}
      <div x-show="editing" x-cloak style="display:flex;align-items:flex-start;gap:4px;">
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
        <button type="button" @click="editing = false" style="border:none;background:rgba(51,46,158,.08);color:#332E9E;border-radius:6px;width:24px;height:24px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;font-size:.75rem;"><i class="ph ph-check"></i></button>
      </div>
    </div>
  </div>
</div>
