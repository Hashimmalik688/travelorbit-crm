@extends('layouts/contentNavbarLayout')

@section('title', 'Customers')

@section('content')
<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>All Customers</h1>
        </div>
    </div>

    <div class="to-filter-bar">
        <form method="GET" action="{{ route('customers') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ph ph-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or phone..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="ph ph-magnifying-glass me-1"></i> Search</button>
                    @if ($search)
                        <a href="{{ route('customers') }}" class="btn btn-outline-secondary btn-sm"><i class="ph ph-x me-1"></i> Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>WhatsApp</th>
                        <th>Email</th>
                        <th class="text-center">Bookings</th>
                        <th class="text-end">Total Spent</th>
                        <th>Last Booking</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle">
                                            {{ strtoupper(substr($customer->booker_name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="fw-semibold">{{ $customer->booker_name }}</span>
                                </div>
                            </td>
                            <td>{{ $customer->booker_mobile }}</td>
                            <td>{{ $customer->booker_whatsapp ?? '-' }}</td>
                            <td>{{ $customer->booker_email ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-label-primary rounded-pill">{{ $customer->total_bookings }}</span>
                            </td>
                            <td class="text-end fw-semibold">£{{ number_format($customer->total_spent, 0) }}</td>
                            <td>{{ $customer->last_booking_date ? \Carbon\Carbon::parse($customer->last_booking_date)->format('d M Y') : '-' }}</td>
                            <td>
                                <a href="{{ route('bookings.index', ['search' => $customer->booker_mobile]) }}" class="btn btn-sm btn-icon btn-outline-primary" title="View bookings">
                                    <i class="ph ph-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-users"></i></div>
                                    <h5>{{ $search ? 'No customers found matching "' . $search . '"' : 'No customers yet' }}</h5>
                                    <p>Customers appear here once bookings are created.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
