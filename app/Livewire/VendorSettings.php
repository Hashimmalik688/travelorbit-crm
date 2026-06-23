<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class VendorSettings extends Component
{
    /** @var array<int, array{value: string, label: string}> */
    public array $vendors = [];
    public string $newVendorValue = '';
    public string $newVendorLabel = '';

    public function mount(): void
    {
        $this->vendors = Setting::getValue('vendor_options', [
            ['value' => 'Direct',        'label' => 'Direct (Airline)'],
            ['value' => 'Dnata',         'label' => 'Dnata'],
            ['value' => 'Midlands Air',  'label' => 'Midlands Air'],
            ['value' => 'HFG',           'label' => 'HFG'],
            ['value' => 'Global Travel', 'label' => 'Global Travel'],
            ['value' => 'Jac Travel',    'label' => 'Jac Travel'],
            ['value' => 'Portman',       'label' => 'Portman'],
            ['value' => 'Hays Travel',   'label' => 'Hays Travel'],
            ['value' => 'Trailfinders',  'label' => 'Trailfinders'],
            ['value' => 'Other',         'label' => 'Other'],
        ]);
    }

    public function addVendor(): void
    {
        $label = trim($this->newVendorLabel);
        $value = trim($this->newVendorValue) ?: $label;

        if ($label === '') {
            return;
        }

        // Check for duplicate
        foreach ($this->vendors as $v) {
            if (mb_strtolower($v['value']) === mb_strtolower($value)) {
                session()->flash('error', 'Vendor "' . $value . '" already exists.');
                return;
            }
        }

        $this->vendors[] = ['value' => $value, 'label' => $label];
        $this->save();
        $this->newVendorValue = '';
        $this->newVendorLabel = '';
        session()->flash('success', 'Vendor "' . $label . '" added.');
    }

    public function removeVendor(int $index): void
    {
        $label = $this->vendors[$index]['label'] ?? '';
        unset($this->vendors[$index]);
        $this->vendors = array_values($this->vendors);
        $this->save();
        session()->flash('success', 'Vendor "' . $label . '" removed.');
    }

    private function save(): void
    {
        Setting::setValue('vendor_options', $this->vendors);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.vendor-settings');
    }
}
