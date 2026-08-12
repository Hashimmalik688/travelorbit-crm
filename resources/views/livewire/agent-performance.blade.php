<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>{{ $canViewAll ? 'Agent Performance' : 'My Performance' }}</h1>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card animate-in" style="margin-bottom:1rem;">
        <div class="card-body d-flex flex-wrap align-items-end gap-3">
            @if ($canViewAll)
                <div>
                    <label class="form-label mb-1" style="font-size:0.864rem;color:#5A6080;font-weight:600;">Agent</label>
                    <select wire:model.live="agentId" class="form-select form-select-sm" style="min-width:210px;border-radius:10px;">
                        <option value="">All agents</option>
                        @foreach ($agentUsers as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label mb-1" style="font-size:0.864rem;color:#5A6080;font-weight:600;">Month</label>
                    <input type="month" wire:model.live="month" max="{{ now()->format('Y-m') }}"
                           class="form-control form-control-sm" style="min-width:170px;border-radius:10px;">
                </div>
            @else
                <div>
                    <label class="form-label d-block mb-1" style="font-size:0.864rem;color:#5A6080;font-weight:600;">Period</label>
                    <div class="btn-group" role="group">
                        <button type="button" wire:click="setMonth('current')"
                            class="btn btn-sm {{ $month === now()->format('Y-m') ? 'btn-orange' : 'btn-outline-secondary' }}">
                            This Month
                        </button>
                        <button type="button" wire:click="setMonth('last')"
                            class="btn btn-sm {{ $month === now()->subMonthNoOverflow()->format('Y-m') ? 'btn-orange' : 'btn-outline-secondary' }}">
                            Last Month
                        </button>
                    </div>
                </div>
            @endif
            <div class="ms-auto text-muted" style="font-size:0.96rem;">
                Showing <span class="fw-semibold" style="color:#332E9E;">{{ $monthLabel }}</span>
                · <span class="fw-semibold">{{ $totals['count'] }}</span> booking{{ $totals['count'] === 1 ? '' : 's' }}
            </div>
        </div>
    </div>

    {{-- ── Booking type filter — always visible, even when the current filter yields no rows ── --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" wire:click="$set('typeFilter', '')" style="font-size:0.816rem;font-weight:700;border-radius:20px;padding:5px 12px;border:none;cursor:pointer;{{ !$typeFilter ? 'background:#332E9E;color:#fff;' : 'background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.10);color:#332E9E;' }}">
            All Types
        </button>
        @foreach ($breakdown['bookingType'] as $label => $count)
            @php $typeKey = strtolower($label); @endphp
            <button type="button" wire:click="$set('typeFilter', '{{ $typeKey }}')" class="d-flex align-items-center gap-1" style="font-size:0.816rem;font-weight:700;border-radius:20px;padding:5px 12px;border:none;cursor:pointer;{{ $typeFilter === $typeKey ? 'background:#332E9E;color:#fff;' : 'background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.10);color:#332E9E;' }}">
                <span style="{{ $typeFilter === $typeKey ? 'color:#fff;' : 'color:#475569;font-weight:400;' }}">{{ $label }}</span>
                <span>{{ $count }}</span>
            </button>
        @endforeach
    </div>

    {{-- ── Booking-level performance table ── --}}
    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="font-size:0.888rem;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Number</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Route</th>
                        <th>Locator</th>
                        <th>Airline</th>
                        <th>Supplier</th>
                        @if ($showAgent)<th>Agent</th>@endif
                        <th class="text-end">Sold</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end">CC Charges</th>
                        <th class="text-end">Margin</th>
                        <th class="text-end">Net Margin</th>
                        <th class="text-end">Shared</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                        <tr>
                            <td style="white-space:nowrap;">{{ $r['date']->format('d M') }}</td>
                            <td style="white-space:nowrap;">
                                @if ($r['isDateChange'])
                                    <span class="badge" style="background:rgba(217,119,6,.12);color:#B45309;font-weight:700;font-size:0.672rem;margin-right:4px;">DC</span>
                                @endif
                                <a href="{{ route('bookings.show', $r['booking']) }}" style="color:#332E9E;text-decoration:none;font-weight:600;">
                                    #{{ $r['bookingNumber'] }}
                                </a>
                            </td>
                            <td style="white-space:nowrap;">
                                <span style="font-size:0.792rem;font-weight:600;background:rgba(51,46,158,.06);border:1px solid rgba(51,46,158,.12);color:#332E9E;border-radius:8px;padding:2px 9px;">{{ ucfirst($r['type'] ?: '—') }}</span>
                            </td>
                            <td>{{ $r['customer'] }}</td>
                            <td style="white-space:nowrap;">{{ $r['route'] ?? '—' }}</td>
                            <td>{{ $r['locator'] }}</td>
                            <td>{{ $r['airline'] }}</td>
                            <td>{{ $r['supplier'] }}</td>
                            @if ($showAgent)<td>{{ $r['agent'] }}</td>@endif
                            <td class="text-end">£{{ number_format($r['sold'], 2) }}</td>
                            <td class="text-end">£{{ number_format($r['cost'], 2) }}</td>
                            <td class="text-end">£{{ number_format($r['ccCharges'], 2) }}</td>
                            <td class="text-end fw-semibold {{ $r['marginNoCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($r['marginNoCc'], 2) }}</td>
                            <td class="text-end fw-semibold {{ $r['marginCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($r['marginCc'], 2) }}</td>
                            <td class="text-end">
                                @if ($r['sharedOut'] > 0)
                                    <span class="text-danger fw-semibold">−£{{ number_format($r['sharedOut'], 2) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $showAgent ? 15 : 14 }}">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-calendar-x"></i></div>
                                    <h5>No bookings this month</h5>
                                    <p>No bookings were issued in {{ $monthLabel }}.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($rows->isNotEmpty())
                    <tfoot>
                        <tr style="border-top:2px solid rgba(51,46,158,.15);background:rgba(51,46,158,.03);font-weight:700;">
                            <td colspan="{{ $showAgent ? 9 : 8 }}" style="color:#0F172A;">TOTAL</td>
                            <td class="text-end">£{{ number_format($totals['sold'], 2) }}</td>
                            <td class="text-end">£{{ number_format($totals['cost'], 2) }}</td>
                            <td class="text-end">£{{ number_format($totals['ccCharges'], 2) }}</td>
                            <td class="text-end {{ $totals['marginNoCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($totals['marginNoCc'], 2) }}</td>
                            <td class="text-end {{ $totals['marginCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($totals['marginCc'], 2) }}</td>
                            <td class="text-end {{ $totals['sharedOut'] > 0 ? 'text-danger' : '' }}">
                                {{ $totals['sharedOut'] > 0 ? '−£'.number_format($totals['sharedOut'], 2) : '—' }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Shown whenever there's anything to report for the period — not just
         bookings. An agent with zero issued bookings this month but a
         released margin claim (see AgentPerformance::claimsQuery) still has
         a Net Margin worth showing, so this can't be gated on $rows alone. --}}
    @if ($rows->isNotEmpty() || $totals['claims'] > 0 || $totals['deductions'] > 0 || $totals['sharedIn'] > 0 || $totals['sharedOut'] > 0)
        <div class="d-flex flex-wrap gap-3 mt-3">
            {{-- ── Financial summary ── --}}
            <div class="card animate-in" style="flex:1 1 320px;max-width:400px;">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                        <span class="text-muted">Total Sold</span>
                        <span class="fw-semibold">£{{ number_format($totals['sold'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                        <span class="text-muted">Total Cost</span>
                        <span class="fw-semibold">£{{ number_format($totals['cost'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                        <span class="text-muted">Gross Margin</span>
                        <span class="fw-semibold {{ $totals['marginNoCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($totals['marginNoCc'], 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                        <span class="text-danger">Deductions</span>
                        <span class="fw-semibold">{{ $totals['deductions'] > 0 ? '−£'.number_format($totals['deductions'], 2) : '£0.00' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                        <span class="text-danger">CC Charges</span>
                        <span class="fw-semibold">{{ $totals['ccCharges'] > 0 ? '−£'.number_format($totals['ccCharges'], 2) : '£0.00' }}</span>
                    </div>
                    @if ($totals['sharedOut'] > 0)
                        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                            <span class="text-muted">Margin Shared Out</span>
                            <span class="fw-semibold text-danger">−£{{ number_format($totals['sharedOut'], 2) }}</span>
                        </div>
                    @endif
                    @if ($totals['sharedIn'] > 0)
                        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                            <span class="text-muted">Margin Shared In</span>
                            <span class="fw-semibold text-success">+£{{ number_format($totals['sharedIn'], 2) }}</span>
                        </div>
                    @endif
                    @if ($totals['claims'] > 0)
                        <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid rgba(51,46,158,.08);">
                            <span class="text-muted">Claimed Margin</span>
                            <span class="fw-semibold text-success">+£{{ number_format($totals['claims'], 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between py-2">
                        <span class="fw-semibold" style="color:#0F172A;">Net Margin</span>
                        <span class="fw-bold {{ $totals['netMarginCc'] >= 0 ? 'text-success' : 'text-danger' }}">£{{ number_format($totals['netMarginCc'], 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- ── Lead source / booking type breakdown ── --}}
            <div class="card animate-in" style="flex:2 1 480px;">
                <div class="card-body py-2">
                    @if (count($breakdown['leadSource']) || $breakdown['dateChange'])
                        <div class="text-muted mb-1" style="font-size:0.768rem;font-weight:700;letter-spacing:.02em;text-transform:uppercase;">Lead Source</div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($breakdown['leadSource'] as $label => $count)
                                <span class="d-flex align-items-center gap-1" style="font-size:0.816rem;background:rgba(51,46,158,.05);border:1px solid rgba(51,46,158,.10);border-radius:8px;padding:4px 10px;">
                                    <span class="text-muted">{{ $label }}</span>
                                    <span class="fw-bold" style="color:#332E9E;">{{ $count }}</span>
                                </span>
                            @endforeach
                            @if ($breakdown['dateChange'])
                                <span class="d-flex align-items-center gap-1" style="font-size:0.816rem;background:rgba(217,119,6,.06);border:1px solid rgba(217,119,6,.14);border-radius:8px;padding:4px 10px;">
                                    <span class="text-muted">Date Change</span>
                                    <span class="fw-bold" style="color:#B45309;">{{ $breakdown['dateChange'] }}</span>
                                </span>
                            @endif
                        </div>
                    @endif

                </div>
            </div>
        </div>
    @endif

    {{-- ── Deductions ── --}}
    @if (!$showAgent && ($deductionsList->isNotEmpty() || $canApplyDeduction))
        <div class="card animate-in mt-3">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="ph ph-minus-circle" style="color:#DC2626;font-size:1.08rem;"></i>
                        <span class="fw-bold" style="color:#0F172A;">Deductions</span>
                    </div>
                    @if ($canApplyDeduction)
                        <button type="button" wire:click="openDeduction" style="background:rgba(220,38,38,.06);color:#DC2626;border:1px solid rgba(220,38,38,.16);border-radius:9px;padding:6px 14px;font-size:0.816rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;">
                            <i class="ph ph-plus-circle"></i> Apply Deduction
                        </button>
                    @endif
                </div>

                @forelse ($deductionsList as $d)
                    <div class="d-flex align-items-center gap-2 mb-2 px-2 py-2" style="background:#FAFBFF;border-radius:8px;border:1px solid rgba(51,46,158,.05);">
                        <i class="ph ph-receipt" style="color:#DC2626;font-size:0.96rem;flex-shrink:0;"></i>
                        <div class="flex-grow-1">
                            <span class="fw-semibold" style="font-size:0.864rem;color:#1E293B;">−£{{ number_format($d->amount, 2) }} — {{ $d->reason }}</span>
                            <span class="d-block" style="font-size:0.744rem;color:#475569;">
                                {{ $d->deduction_date->format('d M Y') }} · applied by {{ $d->appliedBy?->name ?: '—' }}
                                @if ($d->note) · {{ $d->note }} @endif
                            </span>
                        </div>
                        @if ($canApplyDeduction)
                            <button type="button" wire:click="removeDeduction({{ $d->id }})" onclick="return confirm('Remove this deduction?')" style="background:rgba(220,38,38,.08);border:none;color:#DC2626;border-radius:5px;padding:2px 8px;font-size:0.72rem;cursor:pointer;flex-shrink:0;">✕</button>
                        @endif
                    </div>
                @empty
                    <p class="mb-0 text-muted" style="font-size:0.84rem;">No deductions applied {{ $monthLabel }}.</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Claimed margin lives solely in the Financial Summary card's total
         above — same treatment whether viewing all agents or one — no
         separate itemized list here. --}}
</div>

{{-- APPLY DEDUCTION MODAL --}}
@if($deductionOpen)
  <div style="position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:99999;display:flex;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);">
    <div style="background:#fff;border-radius:20px;width:100%;max-width:440px;box-shadow:0 24px 70px rgba(15,23,42,.28);overflow:hidden;">
      <div style="background:linear-gradient(135deg,#B91C1C 0%,#DC2626 60%,#EF4444 100%);padding:22px 26px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-30px;right:-20px;width:110px;height:110px;border-radius:50%;background:rgba(255,255,255,.08);"></div>
        <div class="d-flex align-items-start justify-content-between" style="position:relative;">
            <div class="d-flex align-items-center gap-3">
                <div style="width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.16);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ph ph-minus-circle" style="font-size:1.32rem;color:#fff;"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0" style="font-size:1.104rem;color:#fff;">Apply Deduction</h5>
                    <div style="font-size:0.792rem;color:rgba(255,255,255,.75);">{{ $monthLabel }} performance</div>
                </div>
            </div>
            <button type="button" wire:click="$set('deductionOpen', false)" style="background:rgba(255,255,255,.14);color:#fff;border:none;border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:0.96rem;flex-shrink:0;">✕</button>
        </div>
      </div>
      <div style="padding:24px 26px 26px;">
        <div class="mb-3">
            <label class="form-label mb-1" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;">Amount <span style="color:#DC2626;">*</span></label>
            <div style="position:relative;">
                <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#DC2626;font-size:0.936rem;font-weight:700;">£</span>
                <input type="number" wire:model="deductionAmount" style="width:100%;border:1.5px solid #DC2626;border-radius:7px;padding:6px 10px 6px 26px;font-size:0.984rem;font-weight:700;outline:none;" placeholder="0.00" min="0.01" step="0.01">
            </div>
            @error('deductionAmount') <div style="font-size:0.816rem;color:#DC2626;margin-top:4px;display:flex;align-items:center;gap:4px;"><i class="ph ph-warning-circle"></i>{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label mb-1" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;">Reason <span style="color:#DC2626;">*</span></label>
            <input type="text" wire:model="deductionReason" style="width:100%;border:1.5px solid #DC2626;border-radius:7px;padding:6px 10px;font-size:0.912rem;outline:none;" placeholder="e.g. Missed target, chargeback, error correction">
            @error('deductionReason') <div style="font-size:0.816rem;color:#DC2626;margin-top:4px;display:flex;align-items:center;gap:4px;"><i class="ph ph-warning-circle"></i>{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label class="form-label mb-1" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;">Date <span style="color:#DC2626;">*</span></label>
            <input type="date" wire:model="deductionDate" style="width:100%;border:1.5px solid #DC2626;border-radius:7px;padding:6px 10px;font-size:0.912rem;outline:none;">
            @error('deductionDate') <div style="font-size:0.816rem;color:#DC2626;margin-top:4px;display:flex;align-items:center;gap:4px;"><i class="ph ph-warning-circle"></i>{{ $message }}</div> @enderror
        </div>
        <div class="mb-1">
            <label class="form-label mb-1" style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#475569;">Note (optional)</label>
            <textarea wire:model="deductionNote" rows="2" style="width:100%;border:1.5px solid #DC2626;border-radius:7px;padding:6px 10px;font-size:0.912rem;outline:none;resize:none;" placeholder="Additional context"></textarea>
            @error('deductionNote') <div style="font-size:0.816rem;color:#DC2626;margin-top:4px;display:flex;align-items:center;gap:4px;"><i class="ph ph-warning-circle"></i>{{ $message }}</div> @enderror
        </div>
        <div class="d-flex gap-2 justify-content-end mt-4 pt-3" style="border-top:1px solid rgba(220,38,38,.08);">
            <button type="button" wire:click="$set('deductionOpen', false)" style="background:transparent;border:1.5px solid rgba(51,46,158,.15);color:#475569;border-radius:10px;padding:9px 22px;font-size:0.876rem;font-weight:600;cursor:pointer;">Cancel</button>
            <button type="button" wire:click="saveDeduction" style="background:linear-gradient(135deg,#B91C1C,#DC2626);color:#fff;border:none;border-radius:10px;padding:9px 24px;font-size:0.876rem;font-weight:700;cursor:pointer;box-shadow:0 4px 14px rgba(220,38,38,.3);display:flex;align-items:center;gap:6px;">
                <i class="ph ph-check-circle"></i> Apply
            </button>
        </div>
      </div>
    </div>
  </div>
@endif
