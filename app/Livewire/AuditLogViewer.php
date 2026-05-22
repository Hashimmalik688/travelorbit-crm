<?php

namespace App\Livewire;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public $actionFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = ['actionFilter', 'dateFrom', 'dateTo'];

    public function mount()
    {
        abort_if(Auth::user()->role !== 'admin', 403, 'Only administrators can access this page.');
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::query()
            ->with(['user', 'booking'])
            ->when($this->actionFilter, function ($query) {
                $query->where('action', $this->actionFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('livewire.audit-log-viewer', [
            'logs' => $logs,
        ]);
    }
}
