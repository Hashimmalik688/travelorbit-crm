<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Payment Schedule</h1>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Stat cards - unified to-stat pattern --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-indigo">
                <div class="to-stat-body">
                    <div class="to-stat-label">Collected This Month</div>
                    <div class="to-stat-value">£{{ number_format($totalCollectedThisMonth, 0) }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-money"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-amber">
                <div class="to-stat-body">
                    <div class="to-stat-label">Outstanding</div>
                    <div class="to-stat-value">£{{ number_format($totalOutstanding, 0) }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-wallet"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-red">
                <div class="to-stat-body">
                    <div class="to-stat-label">Overdue</div>
                    <div class="to-stat-value">{{ $totalOverdueCount }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-warning-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6 animate-in">
            <div class="to-stat accent-blue">
                <div class="to-stat-body">
                    <div class="to-stat-label">Due Next 7 Days</div>
                    <div class="to-stat-value">{{ $next7DaysDueCount }}</div>
                </div>
                <div class="to-stat-icon"><i class="ph ph-calendar-dots"></i></div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="to-filter-bar">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" placeholder="Search booking number or booker name..." wire:model.live.debounce.300ms="search">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <x-styled-select modelName="statusFilter" :options="[['value'=>'','label'=>'All Statuses'],['value'=>'pending','label'=>'Pending'],['value'=>'paid','label'=>'Paid'],['value'=>'overdue','label'=>'Overdue']]" placeholder="All Statuses" :live="true" />
            </div>
            <div class="col-md-3 d-flex align-items-end">
                @if ($search || $statusFilter)
                    <button class="btn btn-outline-secondary btn-sm w-100" wire:click="$set('search', ''); $set('statusFilter', '')">
                        <i class="ph ph-x me-1"></i> Clear
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Booking #</th>
                        <th>Booker</th>
                        <th>Installment</th>
                        <th class="text-end">Amount Due</th>
                        <th>Due Date</th>
                        <th>Paid Date</th>
                        <th>Status</th>
                        <th>Mode</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                        <tr>
                            <td>
                                <a href="{{ route('bookings.show', $schedule->booking) }}" class="fw-semibold">
                                    #{{ $schedule->booking->booking_number ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ $schedule->booking->booker_name ?? 'N/A' }}</td>
                            <td>{{ $schedule->installment_number }}</td>
                            <td class="text-end fw-semibold">£{{ number_format($schedule->amount, 2) }}</td>
                            <td>{{ $schedule->due_date?->format('d M Y') ?? '-' }}</td>
                            <td>{{ $schedule->paid_date?->format('d M Y') ?? '-' }}</td>
                            <td>
                                @if ($schedule->status === 'paid')
                                    <span class="badge bg-label-success">Paid</span>
                                @elseif ($schedule->status === 'pending')
                                    <span class="badge bg-label-warning">Pending</span>
                                @elseif ($schedule->status === 'overdue')
                                    <span class="badge bg-label-danger">Overdue</span>
                                @endif
                            </td>
                            <td>{{ $schedule->payment_mode ? ucfirst(str_replace('_', ' ', $schedule->payment_mode)) : '-' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if (in_array($schedule->status, ['pending', 'overdue']))
                                        <button class="btn btn-sm btn-soft-primary" wire:click="markAsPaid({{ $schedule->id }})">
                                            <i class="ph ph-check-circle me-1"></i> Mark Paid
                                        </button>
                                    @endif
                                    @if ($schedule->status === 'pending' && $schedule->due_date && $schedule->due_date->isPast())
                                        <button class="btn btn-sm btn-outline-danger" wire:click="markOverdue({{ $schedule->id }})">
                                            <i class="ph ph-alert-circle me-1"></i> Overdue
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-credit-card"></i></div>
                                    <h5>No payment schedules found</h5>
                                    <p>Payment schedules will appear here when bookings have installment plans.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $schedules->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mark Payment as Paid</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <x-styled-select modelName="paymentMode" :options="[['value'=>'','label'=>'Select Payment Mode'],['value'=>'cash','label'=>'Cash'],['value'=>'bank_transfer','label'=>'Bank Transfer'],['value'=>'stripe','label'=>'Stripe'],['value'=>'klarna','label'=>'Klarna'],['value'=>'card','label'=>'Card']]" placeholder="Select Payment Mode" />
                            @error('paymentMode') <div class="text-danger" style="font-size:0.936rem;margin-top:4px;">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Paid Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('paidDate') is-invalid @enderror" wire:model="paidDate">
                            @error('paidDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn btn-orange" wire:click="confirmPayment">
                            <i class="ph ph-check-circle me-1"></i> Confirm Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
