@extends('layouts/contentNavbarLayout')

@section('title', 'All Bookings')

@section('content')
    @livewire('booking-index', ['filterUserId' => $filterUserId ?? null, 'myBookingsOnly' => $myBookingsOnly ?? false])
@endsection
