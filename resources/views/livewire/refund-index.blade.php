<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Refunds</h1>
        </div>
    </div>

    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Booker</th>
                        <th>Amount</th>
                        <th>Requested</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($refunds ?? [] as $refund)
                        <tr>
                            <td><a href="#" class="fw-semibold">#{{ $refund->booking->booking_number ?? 'N/A' }}</a></td>
                            <td>{{ $refund->booking->booker_name ?? 'N/A' }}</td>
                            <td>£{{ number_format($refund->amount, 2) }}</td>
                            <td>{{ $refund->created_at?->format('d M Y') ?? '-' }}</td>
                            <td><span class="badge bg-label-warning">{{ ucfirst($refund->status ?? 'pending') }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-icon btn-outline-primary"><i class="ph ph-eye"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-arrow-u-up-left"></i></div>
                                    <h5>No refunds</h5>
                                    <p>Refund requests will appear here when bookings are cancelled.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
