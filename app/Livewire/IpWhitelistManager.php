<?php

namespace App\Livewire;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class IpWhitelistManager extends Component
{
    public array  $ips      = [];
    public string $newIp    = '';
    public string $newLabel = '';
    public bool   $enabled  = false;

    private static string $file = 'ip-whitelist.json';

    public function mount(): void
    {
        $data = $this->load();
        $this->ips     = $data['ips']     ?? [];
        $this->enabled = $data['enabled'] ?? false;
    }

    private function storagePath(): string
    {
        return storage_path('app/' . self::$file);
    }

    private function load(): array
    {
        if (!file_exists($this->storagePath())) return ['enabled' => false, 'ips' => []];
        return json_decode(file_get_contents($this->storagePath()), true) ?? [];
    }

    private function persist(): void
    {
        file_put_contents($this->storagePath(), json_encode([
            'enabled' => $this->enabled,
            'ips'     => $this->ips,
        ], JSON_PRETTY_PRINT));
    }

    public function addIp(): void
    {
        $ip = trim($this->newIp);
        if (empty($ip)) return;

        // Basic format validation
        if (!filter_var($ip, FILTER_VALIDATE_IP) && !str_contains($ip, '/')) {
            $this->addError('newIp', 'Invalid IP address or CIDR range.');
            return;
        }

        // Check duplicate
        foreach ($this->ips as $entry) {
            if ($entry['ip'] === $ip) {
                $this->addError('newIp', 'This IP is already in the list.');
                return;
            }
        }

        $this->ips[] = [
            'ip'    => $ip,
            'label' => trim($this->newLabel) ?: $ip,
            'added' => now()->format('d M Y'),
        ];
        $this->persist();

        AuditLog::logAction('ip_whitelist_add', Auth::user(), null, null, "Added IP {$ip} to whitelist");

        $this->newIp = $this->newLabel = '';
        $this->resetErrorBag();
        session()->flash('success', "IP {$ip} added.");
    }

    public function removeIp(int $index): void
    {
        $ip = $this->ips[$index]['ip'] ?? '?';
        unset($this->ips[$index]);
        $this->ips = array_values($this->ips);
        $this->persist();
        AuditLog::logAction('ip_whitelist_remove', Auth::user(), null, null, "Removed IP {$ip} from whitelist");
        session()->flash('success', "IP {$ip} removed.");
    }

    public function toggleEnabled(): void
    {
        $this->enabled = !$this->enabled;
        $this->persist();
        AuditLog::logAction('ip_whitelist_toggle', Auth::user(), null, null, 'IP whitelist '.($this->enabled ? 'enabled' : 'disabled'));
    }

    public function render()
    {
        return view('livewire.ip-whitelist-manager');
    }
}
