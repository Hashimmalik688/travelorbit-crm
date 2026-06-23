import flatpickr from 'flatpickr';
import 'flatpickr/dist/themes/light.css';

document.addEventListener('alpine:init', () => {
  Alpine.data('datePicker', (modelName, isDateTime) => ({
    fp: null,
    isOpen: false,
    init() {
      const el = this.$el.querySelector('.to-date-native');
      if (!el) return;
      this.fp = flatpickr(el, {
        dateFormat: isDateTime ? 'd/m/Y H:i' : 'd/m/Y',
        enableTime: isDateTime,
        time_24hr: true,
        allowInput: false,
        weekNumbers: true,
        onChange: (selectedDates, dateStr) => {
          this.$wire.set(modelName, dateStr);
        },
        onReady: (selectedDates, dateStr, instance) => {
          const initial = this.$wire.get(modelName);
          if (initial) {
            const parsed = flatpickr.parseDate(initial, 'Y-m-d');
            if (parsed) instance.setDate(parsed);
          }
        }
      });
    },
    destroy() { if (this.fp) { this.fp.destroy(); this.fp = null; } }
  }));
});
