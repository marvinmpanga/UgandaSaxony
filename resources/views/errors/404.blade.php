@extends('errors.layout')

@section('code', '404')
@section('title', 'Page Not Found')
@section('eyebrow', 'Error 404')
@section('heading', 'We couldn’t find that page.')
@section('message', 'The page you are looking for may have been moved, renamed, or is no longer available. Use the button below to return to the homepage or explore our events and partnerships.')

@section('secondary-action')
    <a href="{{ url('/contact-us') }}" class="btn btn-outline">Contact us</a>
@endsection
