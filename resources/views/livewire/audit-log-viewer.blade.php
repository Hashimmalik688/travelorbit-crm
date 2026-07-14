<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Activity Log</h1>
        </div>
    </div>

    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs ?? [] as $log)
                        @php
                          $action = $log->action ?? '';
                          $desc = $log->description ?? '';
                          $border = '#94A3B8';
                          $bg = 'transparent';
                          if (stripos($action, 'cancelled') !== false || stripos($action, 'refund') !== false || stripos($action, 'cancel') !== false) {
                              $border = '#DC2626'; $bg = 'rgba(220,38,38,.05)';
                          } elseif (stripos($action, 'approved') !== false || stripos($action, 'received') !== false || stripos($action, 'paid') !== false) {
                              $border = '#16A34A'; $bg = 'rgba(22,163,74,.05)';
                          } elseif (stripos($action, 'requested') !== false || stripos($action, 'charged') !== false || stripos($action, 'raised') !== false) {
                              $border = '#F59E0B'; $bg = 'rgba(245,158,11,.05)';
                          } elseif (stripos($action, 'created') !== false || stripos($action, 'opened') !== false) {
                              $border = '#0EA5E9'; $bg = 'rgba(14,165,233,.05)';
                          } elseif (stripos($action, 'ticket') !== false || stripos($action, 'issued') !== false || stripos($action, 'document') !== false || stripos($action, 'voucher') !== false) {
                              $border = '#7C3AED'; $bg = 'rgba(124,58,237,.05)';
                          } elseif (stripos($action, 'hotel') !== false || stripos($action, 'transfer') !== false) {
                              $border = '#0891B2'; $bg = 'rgba(8,145,178,.05)';
                          }
                          $badgeColor = $border;
                          $badgeBg = $bg === 'transparent' ? 'rgba(148,163,184,.08)' : $bg;
                        @endphp
                        <tr style="border-left:3px solid {{ $border }};">
                            <td>{{ $log->created_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle">{{ strtoupper(substr($log->user?->name ?? 'S', 0, 2)) }}</span>
                                    </div>
                                    <span>{{ $log->user?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td><span class="badge" style="color:{{ $badgeColor }};background:{{ $badgeBg }};">{{ $log->action ?? '-' }}</span></td>
                            <td class="text-muted small">{{ $log->description ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="to-empty">
                                    <div class="to-empty-icon"><i class="ph ph-clipboard-text"></i></div>
                                    <h5>No audit logs</h5>
                                    <p>System activity will be recorded here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (!empty($logs) && method_exists($logs, 'links'))
            <div class="card-footer">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
