<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\BookingComment;
use App\Models\Refund;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BookingShow extends Component
{
    public Booking $booking;
    public $newComment = '';
    public $bookingStatus;

    // Refund modal
    public $showRefundModal = false;
    public $refundAmount = '';
    public $refundReason = '';
    public $refundMethod = 'bank_transfer';

    public function mount(Booking $booking)
    {
        $this->booking = $booking->load([
            'passengers', 'payment', 'documents', 'flightDetail', 'flightCosts', 'hotels',
            'comments' => function ($query) {
                $query->with('user')->orderBy('created_at');
            }
        ]);
        $this->bookingStatus = $booking->booking_status;
    }

    public function updatedBookingStatus($value)
    {
        $oldStatus = $this->booking->booking_status;
        $this->booking->update(['booking_status' => $value]);
        $this->booking->refresh();

        AuditLogger::log(
            Auth::user(),
            $this->booking,
            'status_changed',
            "Status changed to {$value}",
            ['booking_status' => $oldStatus],
            ['booking_status' => $value],
        );

        session()->flash('success', 'Status updated to ' . ucfirst(str_replace('_', ' ', $value)) . '.');
    }

    public function addComment()
    {
        if (!$this->canComment()) {
            return;
        }

        $this->validate([
            'newComment' => 'required|string|max:2000',
        ]);

        BookingComment::create([
            'booking_id' => $this->booking->id,
            'user_id' => Auth::id(),
            'comment' => $this->newComment,
        ]);

        AuditLogger::log(
            Auth::user(),
            $this->booking,
            'comment_added',
            'Comment added',
            null,
            ['comment' => $this->newComment],
        );

        $this->newComment = '';
        $this->booking->load([
            'comments' => function ($query) {
                $query->with('user')->orderBy('created_at');
            }
        ]);
    }

    public function canComment(): bool
    {
        $user = Auth::user();
        return $user->id === $this->booking->user_id || $user->role === 'admin';
    }

    public function requestRefund(): void
    {
        $this->refundAmount = $this->booking->total_sale_price ?? '';
        $this->refundReason = '';
        $this->refundMethod = 'bank_transfer';
        $this->showRefundModal = true;
    }

    public function submitRefund(): void
    {
        $this->validate([
            'refundAmount' => 'required|numeric|min:0.01',
            'refundReason' => 'required|string|max:1000',
            'refundMethod' => 'required|in:cash,bank_transfer,stripe,klarna',
        ]);

        $oldStatus = $this->booking->booking_status;

        Refund::create([
            'booking_id' => $this->booking->id,
            'requested_by' => Auth::id(),
            'refund_amount' => $this->refundAmount,
            'reason' => $this->refundReason,
            'refund_method' => $this->refundMethod,
            'requested_at' => now(),
            'status' => 'requested',
        ]);

        $this->booking->update(['booking_status' => 'refund_queue']);
        $this->bookingStatus = 'refund_queue';
        $this->booking->refresh();

        AuditLogger::log(
            Auth::user(),
            $this->booking,
            'status_changed',
            "Refund requested — status changed from {$oldStatus} to refund_queue",
            ['booking_status' => $oldStatus],
            ['booking_status' => 'refund_queue', 'refund_amount' => $this->refundAmount, 'refund_method' => $this->refundMethod],
        );

        $this->showRefundModal = false;
        session()->flash('success', 'Refund request submitted successfully.');
    }

    public function render()
    {
        return view('livewire.booking-show');
    }
}
