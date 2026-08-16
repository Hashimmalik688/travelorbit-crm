@extends('layouts/contentNavbarLayout')

@section('title', 'Create Booking')

@section('content')
    @livewire('create-booking', ['fromBookingId' => $fromBookingId ?? null])
@endsection
