@extends('errors.layout')

@section('code', '503')
@section('title', 'Temporarily Unavailable')
@section('eyebrow', 'Maintenance')
@section('heading', 'We’ll be right back.')
@section('message', 'The Uganda–Saxony Partnership website is currently undergoing brief maintenance. Please check back in a few minutes.')

@section('secondary-action')
    <button type="button" onclick="window.location.reload()" class="btn btn-outline">Refresh page</button>
@endsection
