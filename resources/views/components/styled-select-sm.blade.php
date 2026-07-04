@props(['modelName', 'placeholder' => '', 'optgroup' => false, 'options' => [], 'live' => false, 'searchable' => false, 'disabled' => false])
<div x-data="initSelect('{{ $modelName }}', {{ json_encode($options) }}, '{{ $placeholder }}', false, {{ $searchable ? 'true' : 'false' }})"
  x-on:click.outside="close()"
  @if(!$disabled)
  x-on:keydown.escape="close()"
  x-on:keydown.down.prevent="navigate('ArrowDown')"
  x-on:keydown.up.prevent="navigate('ArrowUp')"
  x-on:keydown.enter.prevent="selectHighlighted()"
  @endif
  class="to-custom-select-sm"
  @if($disabled) style="opacity:.45;pointer-events:none;" @endif
>
  <select x-ref="native" wire:model{{ $live ? '.live' : '' }}="{{ $modelName }}" class="d-none" style="display:none !important;position:absolute;opacity:0;pointer-events:none;" @disabled($disabled)>
    <option value="">{{ $placeholder }}</option>
    @if($optgroup)
      @foreach($options as $group)
        @foreach($group['options'] as $opt)
          <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
        @endforeach
      @endforeach
    @else
      @foreach($options as $opt)
        @php $v = is_array($opt) ? $opt['value'] : $opt; $l = is_array($opt) ? $opt['label'] : $opt; @endphp
        <option value="{{ $v }}">{{ $l }}</option>
      @endforeach
    @endif
  </select>
  <button type="button" x-on:click="{{ $disabled ? '' : 'toggle()' }}" @disabled($disabled)
    class="to-select-trigger-sm"
    :class="{ 'to-select-trigger-sm--open': open, 'to-select-trigger-sm--placeholder': !label }"
  >
    <span x-text="label || '{{ $placeholder }}'" class="to-select-label"></span>
    <svg class="to-select-chevron-sm" :class="{ 'to-select-chevron-sm--open': open }" width="10" height="10" viewBox="0 0 12 12">
      <path d="M6 8L1 3h10z" fill="currentColor" opacity="0.45"/>
    </svg>
  </button>
  <div
    x-show="open"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="to-select-dropdown-sm"
    :class="{ 'to-select-dropdown-sm--up': dropUp }" x-cloak
  >
    @if($searchable)
      <div style="padding:5px 6px;border-bottom:1px solid rgba(51,46,158,.08);">
        <input type="text" x-model="searchQuery" @click.stop class="to-select-search"
          placeholder="Search..."
          style="width:100%;border:1px solid rgba(51,46,158,.15);border-radius:6px;padding:4px 8px;font-size:.72rem;outline:none;color:#1E293B;background:#fff;">
      </div>
      <div style="max-height:220px;overflow-y:auto;">
        <template x-for="opt in filteredOpts" :key="opt.value ?? opt">
          <button type="button"
            x-on:click.prevent="select(opt.value ?? opt)"
            class="to-select-option-sm"
            :class="{ 'to-select-option-sm--selected': selected == (opt.value ?? opt) }"
            x-text="opt.label ?? opt"
          ></button>
        </template>
        <div x-show="filteredOpts.length === 0" style="padding:8px 10px;font-size:.7rem;color:#94A3B8;text-align:center;">No results</div>
      </div>
    @elseif($optgroup)
      @foreach($options as $group)
        <div class="to-select-optgroup">
          <div class="to-select-optgroup-label-sm">{{ $group['label'] }}</div>
          @foreach($group['options'] as $opt)
            <button type="button"
              x-on:click.prevent="select('{{ $opt['value'] }}')"
              class="to-select-option-sm"
              :class="{ 'to-select-option-sm--selected': selected == '{{ $opt['value'] }}' }"
            >{{ $opt['label'] }}</button>
          @endforeach
        </div>
      @endforeach
    @else
      @foreach($options as $opt)
        @php $v = is_array($opt) ? $opt['value'] : $opt; $l = is_array($opt) ? $opt['label'] : $opt; @endphp
        <button type="button"
          x-on:click.prevent="select('{{ $v }}')"
          class="to-select-option-sm"
          :class="{ 'to-select-option-sm--selected': selected == '{{ $v }}' }"
        >{{ $l }}</button>
      @endforeach
    @endif
  </div>
</div>
