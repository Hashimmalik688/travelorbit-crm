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
  <input type="email" wire:model="{{ $model }}" class="bv-input-inline bv-input-sm" placeholder="{{ $placeholder }}" style="font-size:0.864rem;">
@elseif($type === 'cost')
  <input type="number" wire:model.blur="{{ $rawModel }}" step="0.01" min="0" class="bv-input-inline bv-input-sm" placeholder="{{ $placeholder }}" style="font-size:0.864rem;width:100px;">
@elseif($type === 'rooms')
  <input type="number" wire:model.live="{{ $rawModel }}" min="1" class="bv-input-inline bv-input-sm" placeholder="{{ $placeholder }}" style="font-size:0.864rem;width:70px;">
@else
  <input type="text" wire:model="{{ $model }}" class="bv-input-inline bv-input-sm" placeholder="{{ $placeholder }}" style="font-size:0.864rem;">
@endif
