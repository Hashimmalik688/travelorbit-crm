@extends('layouts/contentNavbarLayout')

@section('title', 'Settings')

@section('content')
<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Settings</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; Settings
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8 animate-in">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">General Settings</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control" value="Travel Orbit">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="admin@travelorbit.co.uk">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" placeholder="+44 20 XXXX XXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Currency</label>
                                <select class="form-select">
                                    <option>GBP (£)</option>
                                    <option>USD ($)</option>
                                    <option>EUR (€)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ph ph-check-circle me-1"></i> Save Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 animate-in" style="animation-delay: 0.08s;">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Info</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Version</span>
                        <span class="fw-semibold small">2.0.0</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">PHP</span>
                        <span class="fw-semibold small">{{ phpversion() }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted small">Laravel</span>
                        <span class="fw-semibold small">{{ app()->version() }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted small">Environment</span>
                        <span class="badge bg-label-{{ app()->environment('production') ? 'success' : 'warning' }}">
                            {{ ucfirst(app()->environment()) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
