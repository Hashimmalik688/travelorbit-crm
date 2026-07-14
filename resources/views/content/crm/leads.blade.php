@extends('layouts/contentNavbarLayout')

@section('title', 'Leads')

@section('content')
<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Leads</h1>
        </div>
        <div class="to-page-header-right">
            <button class="btn btn-orange btn-sm">
                <i class="ph ph-plus me-1"></i> Add Lead
            </button>
        </div>
    </div>

    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">
                            <div class="to-empty">
                                <div class="to-empty-icon"><i class="ph ph-lightning"></i></div>
                                <h5>No leads yet</h5>
                                <p>Add your first lead to start tracking potential customers.</p>
                                <button class="btn btn-orange btn-sm"><i class="ph ph-plus me-1"></i> Add Lead</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
