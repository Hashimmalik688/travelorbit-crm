<?php

namespace App\Livewire;

use App\Models\Setting;
use Carbon\Carbon;
use Livewire\Component;

/**
 * Settings → Attendance: office timing + company holidays.
 *
 * Everything is persisted to the shared `settings` table via Setting::setValue
 * (same store the AttendanceService/Attendance model read from), so no extra
 * tables are involved. `late_threshold_minutes` is derived from the two time
 * fields to keep the service and the model's isLate() in sync.
 */
class AttendanceSettings extends Component
{
    // Timing
    public bool $enabled = true;
    public string $officeStartTime = '09:00';
    public string $lateTime = '09:15';
    public int $shiftDurationHours = 8;
    public int $bufferHours = 1;
    public bool $allowWeekend = false;

    // Holidays: list of ['date' => 'Y-m-d', 'name' => '...']
    public array $holidays = [];
    public string $newHolidayDate = '';
    public string $newHolidayName = '';

    public function mount(): void
    {
        $this->enabled            = (bool) Setting::getValue('attendance_enabled', true);
        $this->officeStartTime    = Setting::getValue('office_start_time', '09:00');
        $this->lateTime           = Setting::getValue('late_time', '09:15');
        $this->shiftDurationHours = (int) Setting::getValue('shift_duration_hours', 8);
        $this->bufferHours        = (int) Setting::getValue('attendance_buffer_hours', 1);
        $this->allowWeekend       = (bool) Setting::getValue('allow_weekend_attendance', false);

        $this->holidays = collect(Setting::getValue('attendance_holidays', []))
            ->sortBy('date')->values()->all();
    }

    public function saveSettings(): void
    {
        $this->validate([
            'officeStartTime'    => ['required', 'date_format:H:i'],
            'lateTime'           => ['required', 'date_format:H:i'],
            'shiftDurationHours' => ['required', 'integer', 'min:1', 'max:24'],
            'bufferHours'        => ['required', 'integer', 'min:0', 'max:12'],
        ]);

        Setting::setValue('attendance_enabled', $this->enabled);
        Setting::setValue('office_start_time', $this->officeStartTime);
        Setting::setValue('late_time', $this->lateTime);
        Setting::setValue('shift_duration_hours', $this->shiftDurationHours);
        Setting::setValue('attendance_buffer_hours', $this->bufferHours);
        Setting::setValue('allow_weekend_attendance', $this->allowWeekend);

        // Keep the model's late check consistent with the late-time field.
        $start = Carbon::createFromFormat('H:i', $this->officeStartTime);
        $late = Carbon::createFromFormat('H:i', $this->lateTime);
        Setting::setValue('late_threshold_minutes', max(0, $start->diffInMinutes($late, false)));

        session()->flash('success', 'Attendance settings saved.');
    }

    public function addHoliday(): void
    {
        $this->validate([
            'newHolidayDate' => ['required', 'date'],
            'newHolidayName' => ['required', 'string', 'max:80'],
        ], [], ['newHolidayDate' => 'date', 'newHolidayName' => 'name']);

        $date = Carbon::parse($this->newHolidayDate)->format('Y-m-d');

        foreach ($this->holidays as $h) {
            if ($h['date'] === $date) {
                session()->flash('error', 'A holiday is already set for ' . $date . '.');
                return;
            }
        }

        $this->holidays[] = ['date' => $date, 'name' => trim($this->newHolidayName)];
        $this->holidays = collect($this->holidays)->sortBy('date')->values()->all();
        Setting::setValue('attendance_holidays', $this->holidays);

        $this->newHolidayDate = '';
        $this->newHolidayName = '';
        session()->flash('success', 'Holiday added.');
    }

    public function removeHoliday(int $index): void
    {
        unset($this->holidays[$index]);
        $this->holidays = array_values($this->holidays);
        Setting::setValue('attendance_holidays', $this->holidays);
        session()->flash('success', 'Holiday removed.');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.attendance-settings');
    }
}
