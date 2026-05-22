@extends('layouts/contentNavbarLayout')

@section('title', 'Sales Reports')

@section('content')
<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Sales Reports</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="#">MIS</a> &rsaquo; Sales Reports
            </div>
        </div>
    </div>

    <div class="card animate-in">
        <div class="card-body">
            <div class="to-empty">
                <div class="to-empty-icon"><i class="ph ph-chart-bar-horizontal"></i></div>
                <h5>MIS Sales Reports</h5>
                <p>Coming soon — detailed business intelligence reports.</p>
            </div>
        </div>
    </div>
</div>
@endsection
