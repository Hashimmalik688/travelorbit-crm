@extends('layouts/contentNavbarLayout')

@section('title', 'Customer Details')

@section('content')
<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Customer Details</h1>
        </div>
    </div>

    <div class="card animate-in">
        <div class="card-body">
            <div class="to-empty">
                <div class="to-empty-icon"><i class="ph ph-user-circle"></i></div>
                <h5>Customer details</h5>
                <p>Select a customer to view their detailed information.</p>
            </div>
        </div>
    </div>
</div>
@endsection
