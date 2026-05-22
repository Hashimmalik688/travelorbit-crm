<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Audit Log</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="#">Settings</a> &rsaquo; Audit Log
            </div>
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
                        <tr>
                            <td>{{ $log->created_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle">{{ strtoupper(substr($log->user?->name ?? 'S', 0, 2)) }}</span>
                                    </div>
                                    <span>{{ $log->user?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td><span class="badge bg-label-primary">{{ $log->action ?? '—' }}</span></td>
                            <td class="text-muted small">{{ $log->description ?? '—' }}</td>
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
