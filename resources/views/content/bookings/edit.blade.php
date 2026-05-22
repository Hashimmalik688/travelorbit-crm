@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Booking #' . ($booking->booking_number ?? ''))

@section('content')
    @livewire('booking-edit', ['booking' => $booking])
@endsection
