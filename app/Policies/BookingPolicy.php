<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    // Admin/Manager bypass
    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role, ['admin', 'manager'])) return true;
        return null;
    }

    public function viewAny(User $user): bool  { return true; }
    public function view(User $user, Booking $booking): bool { return true; }

    public function create(User $user): bool
    {
        return $user->canCreateBooking();
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->canEditBooking($booking);
    }

    public function delete(User $user, Booking $booking): bool
    {
        return false; // only via admin panel
    }

    public function restore(User $user, Booking $booking): bool  { return false; }
    public function forceDelete(User $user, Booking $booking): bool { return false; }

    // ── Workflow actions ──────────────────────────────────────────────

    public function queueForIssuance(User $user, Booking $booking): bool
    {
        return $user->canQueueForIssuance($booking);
    }

    public function removeFromIssuanceQueue(User $user, Booking $booking): bool
    {
        return $user->canManageIssuanceQueue($booking);
    }

    public function markTicketInProcess(User $user, Booking $booking): bool
    {
        return in_array($user->role, ['issuance', 'admin', 'manager'])
            && $booking->canMarkTicketInProcess();
    }

    public function invoice(User $user, Booking $booking): bool
    {
        return $user->canInvoice($booking);
    }

    public function chargePayment(User $user, Booking $booking): bool
    {
        return $user->canChargePayment();
    }

    public function declinePayment(User $user, Booking $booking): bool
    {
        return in_array($user->role, ['accounts', 'admin', 'manager']);
    }

    public function restoreToPending(User $user, Booking $booking): bool
    {
        return in_array($user->role, ['issuance', 'admin', 'manager'])
            && $booking->canRestoreToPending();
    }
}
