@extends('errors.layout')

@section('code', '500')
@section('title', 'Server Error')
@section('eyebrow', 'Server Error')
@section('heading', 'Something went wrong on our end.')
@section('message', 'We encountered an unexpected error while loading this page. Please try again in a moment, or return to the homepage while we resolve the issue.')

@section('secondary-action')
    <button type="button" onclick="window.location.reload()" class="btn btn-outline">Try again</button>
@endsection
