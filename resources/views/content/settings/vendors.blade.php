@extends('layouts/contentNavbarLayout')
@section('title', 'Vendor Settings')
@section('content')

<div class="mb-5">
  <div style="font-size:0.864rem;color:#475569;margin-bottom:4px;"><a href="{{ route('settings') }}" style="color:#475569;text-decoration:none;">Settings</a> › Vendors</div>
  <h1 style="font-size:1.55rem;font-weight:800;color:#0F172A;letter-spacing:-.03em;margin:0;">Vendor Settings</h1>
</div>

@livewire('vendor-settings')

@endsection
