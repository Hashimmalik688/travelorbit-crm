@extends('layouts/contentNavbarLayout')

@section('title', 'Performance Reports')

@section('content')
<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Performance Reports</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="#">MIS</a> &rsaquo; Performance
            </div>
        </div>
    </div>

    <div class="card animate-in">
        <div class="card-body">
            <div class="to-empty">
                <div class="to-empty-icon"><i class="ph ph-presentation-chart"></i></div>
                <h5>MIS Performance Reports</h5>
                <p>Coming soon — KPI dashboards and agent analytics.</p>
            </div>
        </div>
    </div>
</div>
@endsection
