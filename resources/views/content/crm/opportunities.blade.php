@extends('layouts/contentNavbarLayout')

@section('title', 'Opportunities')

@section('content')
<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Opportunities</h1>
        </div>
        <div class="to-page-header-right">
            <button class="btn btn-orange btn-sm">
                <i class="ph ph-plus me-1"></i> Add Opportunity
            </button>
        </div>
    </div>

    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Opportunity</th>
                        <th>Customer</th>
                        <th>Value</th>
                        <th>Stage</th>
                        <th>Close Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6">
                            <div class="to-empty">
                                <div class="to-empty-icon"><i class="ph ph-target"></i></div>
                                <h5>No opportunities yet</h5>
                                <p>Track sales opportunities as they progress through your pipeline.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
