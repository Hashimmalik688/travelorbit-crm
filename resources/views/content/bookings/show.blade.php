@extends('layouts/contentNavbarLayout')

@section('title', 'Booking #' . $booking->booking_number)

@section('content')
    @livewire('booking-show', ['booking' => $booking])
@endsection
