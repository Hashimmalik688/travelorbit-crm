<?php

namespace App\Livewire;

use App\Models\Refund;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class RefundIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    protected $queryString = ['search', 'statusFilter'];

    public function getTotalRequestedProperty()
    {
        return Refund::sum('refund_amount');
    }

    public function getTotalApprovedProperty()
    {
        return Refund::whereIn('status', ['approved', 'processed'])->sum('refund_amount');
    }

    public function getTotalProcessedProperty()
    {
        return Refund::where('status', 'processed')->sum('refund_amount');
    }

    public function getPendingReviewCountProperty()
    {
        return Refund::whereIn('status', ['requested', 'under_review'])->count();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function changeStatus($refundId, $newStatus): void
    {
        $user = Auth::user();
        if (!$user->hasPermission('refunds.manage')) {
            return;
        }

        $refund = Refund::find($refundId);
        if (!$refund) return;

        $oldStatus = $refund->status;
        $refund->update([
            'status' => $newStatus,
            'reviewed_at' => in_array($newStatus, ['under_review', 'approved', 'rejected']) ? now() : $refund->reviewed_at,
            'processed_at' => $newStatus === 'processed' ? now() : $refund->processed_at,
            'processed_by' => in_array($newStatus, ['approved', 'rejected', 'processed']) ? Auth::id() : null,
        ]);

        if ($newStatus === 'rejected' || $newStatus === 'processed') {
            $refund->booking->update([
                'booking_status' => $newStatus === 'processed' ? 'cancelled' : 'confirmed',
            ]);
        }

        AuditLogger::log(
            Auth::user(),
            $refund->booking,
            'status_changed',
            "Refund status changed from {$oldStatus} to {$newStatus}",
            ['status' => $oldStatus],
            ['status' => $newStatus],
        );

        session()->flash('success', "Refund #{$refundId} status updated to " . ucfirst(str_replace('_', ' ', $newStatus)) . '.');
    }

    public function render()
    {
        $refunds = Refund::query()
            ->with(['booking', 'requestedBy', 'processedBy'])
            ->when($this->search, function ($query) {
                $query->whereHas('booking', function ($q) {
                    $q->where('booking_number', 'ILIKE', "%{$this->search}%")
                        ->orWhere('booker_name', 'ILIKE', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.refund-index', [
            'refunds' => $refunds,
        ]);
    }
}
