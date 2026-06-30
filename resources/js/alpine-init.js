document.addEventListener('alpine:init', () => {
  const shouldDropUp = (el, ddHeight) => {
    const trigger = el.querySelector('.to-select-trigger, .to-select-trigger-sm, .to-date-trigger, .to-date-trigger-sm');
    if (!trigger) return false;
    const rect = trigger.getBoundingClientRect();
    return (rect.bottom + ddHeight + 12) > window.innerHeight && (rect.top - ddHeight - 4) > 0;
  };

  Alpine.data('initSelect', (modelName, options, placeholder, forceDropUp = false, searchable = false) => ({
    open: false,
    selected: null,
    options: options,
    highlight: -1,
    label: null,
    dropUp: false,
    syncTimer: null,
    searchQuery: '',
    searchable: searchable,

    get filteredOpts() {
      if (!this.searchable || !this.searchQuery.trim()) return this.options;
      const q = this.searchQuery.toLowerCase();
      const result = [];
      for (const opt of this.options) {
        if (opt.options) {
          const children = opt.options.filter(c => c.label.toLowerCase().includes(q));
          if (children.length) result.push({ ...opt, options: children });
        } else {
          const l = (opt.label !== undefined ? opt.label : opt).toString().toLowerCase();
          if (l.includes(q)) result.push(opt);
        }
      }
      return result;
    },

    init() {
      this.selected = this.$wire.get(modelName);
      this.updateLabel();
      this.$watch('$wire.' + modelName, val => {
        this.selected = val;
        this.updateLabel();
      });
    },

    updateLabel() {
      for (let opt of this.options) {
        if (opt.options) {
          for (let child of opt.options) {
            if (child.value == this.selected) { this.label = child.label; return; }
          }
        } else if (opt.value == this.selected) { this.label = opt.label; return; }
      }
      this.label = null;
    },

    select(val) {
      this.selected = val;
      this.open = false;
      this.highlight = -1;
      this.searchQuery = '';
      this.updateLabel();
      this.$wire.set(modelName, val);
    },

    toggle() {
      if (this.open) { this.open = false; this.searchQuery = ''; return; }
      this.dropUp = forceDropUp || shouldDropUp(this.$el, 270);
      this.open = true;
      this.highlight = -1;
      if (this.searchable) this.$nextTick(() => { const inp = this.$el.querySelector('.to-select-search'); if (inp) inp.focus(); });
    },
    close() { this.open = false; this.highlight = -1; this.searchQuery = ''; },
    navigate(dir) {
      const flat = [];
      for (let o of this.filteredOpts) { if (o.options) flat.push(...o.options); else flat.push(o); }
      if (dir === 'ArrowDown') this.highlight = Math.min(flat.length - 1, this.highlight + 1);
      if (dir === 'ArrowUp') this.highlight = Math.max(-1, this.highlight - 1);
    },
    selectHighlighted() {
      const flat = [];
      for (let o of this.filteredOpts) { if (o.options) flat.push(...o.options); else flat.push(o); }
      if (this.highlight >= 0 && flat[this.highlight]) this.select(flat[this.highlight].value);
    }
  }));

  Alpine.data('datePicker', (modelKey, withTime) => ({
    open: false,
    dropUp: false,
    viewYear: new Date().getFullYear(),
    viewMonth: new Date().getMonth(),
    selectedDate: null,
    display: '',
    timeH: '00',
    timeM: '00',
    typedInput: '',

    initPicker() {
      const v = this.$wire.get(modelKey);
      if (v) this.parseAndSet(v);
    },

    get placeholder() {
      return withTime ? 'DD/MM/YYYY HH:MM' : 'DD/MM/YYYY';
    },

    onTypeInput(e) {
      let val = e.target.value;
      this.typedInput = val;

      // Accept DD/MM/YYYY HH:MM (full) or DD/MM/YYYY (date-only on a datetime field)
      const fullMatch = val.match(/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})$/);
      const dateOnly  = withTime ? val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/) : null;
      const plainMatch = !withTime ? val.match(/^(\d{2})\/(\d{2})\/(\d{4})$/) : null;
      const dateTimeMatch = fullMatch || dateOnly || plainMatch;

      if (dateTimeMatch) {
        const day   = parseInt(dateTimeMatch[1], 10);
        const month = parseInt(dateTimeMatch[2], 10);
        const year  = parseInt(dateTimeMatch[3], 10);
        // If date-only on a datetime field default time to 00:00; 24h only
        const hour  = fullMatch ? parseInt(fullMatch[4], 10) : 0;
        const min   = fullMatch ? parseInt(fullMatch[5], 10) : 0;

        if (year >= 1900 && year <= 2100 && month >= 1 && month <= 12 && day >= 1 && day <= 31) {
          const testDate = new Date(year, month - 1, day);
          if (testDate.getMonth() === month - 1 && testDate.getDate() === day) {
            this.selectedDate = testDate;
            this.viewYear = year; this.viewMonth = month - 1;
            if (withTime) {
              this.timeH = String(hour).padStart(2, '0');
              this.timeM = String(min).padStart(2, '0');
            }
            this.commit();
          }
        }
      }
    },

    onTypeBlur() {
      // On blur, reset to valid display or clear
      if (this.selectedDate) {
        this.commit();
      }
      this.typedInput = this.display;
    },

    onTypeFocus() {
      // Select all text on focus for easy replacement
      this.typedInput = this.display;
    },

    get days() {
      const first = new Date(this.viewYear, this.viewMonth, 1);
      const last = new Date(this.viewYear, this.viewMonth + 1, 0);
      const startDow = (first.getDay() + 6) % 7;
      const today = new Date(); today.setHours(0,0,0,0);
      const days = [];
      for (let i = 0; i < startDow; i++) {
        const d = new Date(this.viewYear, this.viewMonth, -i);
        days.unshift({ num: d.getDate(), inMonth: false, key: 'p-'+i, d: d, isToday: false, isSelected: this.isSameDay(d, this.selectedDate) });
      }
      for (let i = 1; i <= last.getDate(); i++) {
        const d = new Date(this.viewYear, this.viewMonth, i);
        days.push({ num: i, inMonth: true, key: i, d: d, isToday: d.getTime() === today.getTime(), isSelected: this.isSameDay(d, this.selectedDate) });
      }
      let tail = 1;
      while (days.length < 42) {
        const d = new Date(this.viewYear, this.viewMonth + 1, tail);
        days.push({ num: tail, inMonth: false, key: 'n-'+tail, d: d, isToday: false, isSelected: this.isSameDay(d, this.selectedDate) });
        tail++;
      }
      return days;
    },

    isSameDay(a, b) { if (!a || !b) return false; return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate(); },
    prevMonth() { if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; } else this.viewMonth--; },
    nextMonth() { if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; } else this.viewMonth++; },

    pick(day) {
      if (!day.inMonth) { this.viewYear = day.d.getFullYear(); this.viewMonth = day.d.getMonth(); return; }
      this.selectedDate = new Date(day.d);
      this.commit();
      this.open = false;
    },

    commit() {
      if (!this.selectedDate) return;
      const y = this.selectedDate.getFullYear();
      const m = String(this.selectedDate.getMonth() + 1).padStart(2, '0');
      const d = String(this.selectedDate.getDate()).padStart(2, '0');
      const hNum = Math.max(0, Math.min(23, parseInt(String(this.timeH).replace(/[^0-9]/g,'') || '0', 10)));
      const mNum = Math.max(0, Math.min(59, parseInt(String(this.timeM).replace(/[^0-9]/g,'') || '0', 10)));
      const cleanH = String(hNum).padStart(2, '0');
      const cleanM = String(mNum).padStart(2, '0');
      const dbVal = withTime ? `${y}-${m}-${d} ${cleanH}:${cleanM}:00` : `${y}-${m}-${d}`;
      const displayVal = withTime ? `${d}/${m}/${y} ${cleanH}:${cleanM}` : `${d}/${m}/${y}`;
      this.display = displayVal;
      // Only update typedInput if user isn't currently typing (to avoid cursor jumping)
      if (document.activeElement !== this.$refs.dateInput) {
        this.typedInput = displayVal;
      }
      this.$wire.set(modelKey, dbVal);
    },

    parseAndSet(val) {
      if (!val) return;
      if (withTime) {
        const m = val.match(/(\d{4})-(\d{2})-(\d{2})\s*(\d{2}):(\d{2})/);
        if (m) {
          this.selectedDate = new Date(+m[1], +m[2]-1, +m[3]);
          this.viewYear = +m[1]; this.viewMonth = +m[2]-1;
          this.timeH = m[4]; this.timeM = m[5];
          this.display = `${m[3]}/${m[2]}/${m[1]} ${m[4]}:${m[5]}`;
          this.typedInput = this.display;
          return;
        }
      }
      const m = val.match(/(\d{4})-(\d{2})-(\d{2})/);
      if (m) {
        this.selectedDate = new Date(+m[1], +m[2]-1, +m[3]);
        this.viewYear = +m[1]; this.viewMonth = +m[2]-1;
        this.display = `${m[3]}/${m[2]}/${m[1]}`;
        this.typedInput = this.display;
      }
    },

    clear() {
      this.selectedDate = null; this.display = ''; this.typedInput = ''; this.$wire.set(modelKey, ''); this.open = false;
    },

    toggle() {
      if (this.open) { this.open = false; return; }
      if (this.selectedDate) { this.viewYear = this.selectedDate.getFullYear(); this.viewMonth = this.selectedDate.getMonth(); }
      const ddH = withTime ? 370 : 330;
      this.dropUp = shouldDropUp(this.$el, ddH);
      this.open = true;
    },
    close() { this.open = false; }
  }));
});
