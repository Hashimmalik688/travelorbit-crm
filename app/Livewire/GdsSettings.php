<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class GdsSettings extends Component
{
    /** @var array<int, array{value: string, label: string}> */
    public array $gdsOptions = [];
    public string $newGdsValue = '';
    public string $newGdsLabel = '';

    public function mount(): void
    {
        $this->gdsOptions = Setting::getValue('gds_options', [
            ['value' => 'AMADEUS',    'label' => 'Amadeus'],
            ['value' => 'GALILEO',    'label' => 'Galileo'],
            ['value' => 'SABRE',      'label' => 'Sabre'],
            ['value' => 'WORLDSPAN',  'label' => 'Worldspan'],
            ['value' => 'APOLLO',     'label' => 'Apollo'],
            ['value' => 'TRAVELPORT', 'label' => 'Travelport'],
        ]);
    }

    public function addGds(): void
    {
        $label = trim($this->newGdsLabel);
        $value = strtoupper(trim($this->newGdsValue) ?: $label);

        if ($label === '') {
            return;
        }

        foreach ($this->gdsOptions as $g) {
            if (mb_strtolower($g['value']) === mb_strtolower($value)) {
                session()->flash('error', 'GDS "' . $value . '" already exists.');
                return;
            }
        }

        $this->gdsOptions[] = ['value' => $value, 'label' => $label];
        $this->save();
        $this->newGdsValue = '';
        $this->newGdsLabel = '';
        session()->flash('success', 'GDS "' . $label . '" added.');
    }

    public function removeGds(int $index): void
    {
        $label = $this->gdsOptions[$index]['label'] ?? '';
        unset($this->gdsOptions[$index]);
        $this->gdsOptions = array_values($this->gdsOptions);
        $this->save();
        session()->flash('success', 'GDS "' . $label . '" removed.');
    }

    private function save(): void
    {
        Setting::setValue('gds_options', $this->gdsOptions);
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.gds-settings');
    }
}
