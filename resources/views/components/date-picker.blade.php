@props(['modelName', 'placeholder' => 'Select date', 'compact' => false, 'isDateTime' => false])

@php
$wrapClass = $compact ? 'to-date-picker-sm' : 'to-date-picker';
$btnClass = $compact ? 'to-date-trigger-sm' : 'to-date-trigger';
$ddClass = $compact ? 'to-date-dropdown-sm-c' : 'to-date-dropdown-c';
$inputClass = $compact ? 'to-date-input-text-sm' : 'to-date-input-text';
@endphp

<div wire:ignore x-data="datePicker('{{ $modelName }}', {{ $isDateTime ? 'true' : 'false' }})" x-init="initPicker()"
  class="{{ $wrapClass }}" x-on:click.outside="close()" x-on:keydown.escape="close()"
>
  <div class="to-date-input-wrap">
    <input type="text"
      x-ref="dateInput"
      class="{{ $inputClass }}"
      x-model="typedInput"
      x-on:input="onTypeInput($event)"
      x-on:focus="onTypeFocus()"
      x-on:blur="onTypeBlur()"
      x-mask="{{ $isDateTime ? '99/99/9999 99:99' : '99/99/9999' }}"
      placeholder="{{ $isDateTime ? 'DD/MM/YYYY HH:MM' : 'DD/MM/YYYY' }}"
      autocomplete="off"
    >
    <button type="button" x-on:click="toggle()" class="to-date-calendar-btn {{ $compact ? 'to-date-calendar-btn-sm' : '' }}">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#332E9E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.4">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
    </button>
  </div>
  <div x-show="open" x-transition class="{{ $ddClass }}"
    :class="{ 'to-date-dropdown-sm-c--up': dropUp }" x-cloak>
    <div class="to-date-nav">
      <button type="button" x-on:click="prevMonth()" class="to-date-nav-btn">&larr;</button>
      <select x-model="viewMonth" class="to-date-select-m">
        <option value="0">January</option><option value="1">February</option><option value="2">March</option>
        <option value="3">April</option><option value="4">May</option><option value="5">June</option>
        <option value="6">July</option><option value="7">August</option><option value="8">September</option>
        <option value="9">October</option><option value="10">November</option><option value="11">December</option>
      </select>
      <input type="number" x-model="viewYear" class="to-date-input-y" min="2000" max="2100">
      <button type="button" x-on:click="nextMonth()" class="to-date-nav-btn">&rarr;</button>
    </div>
    <div class="to-date-dow"><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span></div>
    <div class="to-date-grid">
      <template x-for="day in days" :key="day.key">
        <button type="button" x-on:click="pick(day)" x-text="day.num" class="to-date-day"
          :class="{
            'to-date-day--dim': !day.inMonth,
            'to-date-day--today': day.isToday,
            'to-date-day--selected': day.isSelected,
          }"></button>
      </template>
    </div>
    @if($isDateTime)
      <div class="to-date-time-row" style="display:flex;align-items:center;gap:6px;padding:8px 10px;border-top:1px solid rgba(51,46,158,.06);">
        <span style="font-size:0.816rem;font-weight:600;color:#475569;flex-shrink:0;margin-right:2px;">Time (24h)</span>
        <input type="number" x-model="timeH" x-on:input="commit()" min="0" max="23" step="1"
          style="width:52px;text-align:center;font-weight:700;border-radius:7px;border:1.5px solid rgba(51,46,158,.2);padding:4px 6px;font-size:0.96rem;outline:none;" placeholder="HH">
        <span style="font-size:1.08rem;font-weight:700;color:#475569;">:</span>
        <input type="number" x-model="timeM" x-on:input="commit()" min="0" max="59" step="1"
          style="width:52px;text-align:center;font-weight:700;border-radius:7px;border:1.5px solid rgba(51,46,158,.2);padding:4px 6px;font-size:0.96rem;outline:none;" placeholder="MM">
      </div>
    @endif
    <div class="to-date-footer">
      <button type="button" x-on:click="clear()" class="to-date-clear-btn">Clear</button>
      <button type="button" x-on:click="close()" class="to-date-done-btn">Done</button>
    </div>
  </div>
</div>
