@extends('errors.layout')

@section('code', '403')
@section('title', 'Access Restricted')
@section('eyebrow', 'Access Denied')
@section('heading', 'You don’t have permission to view this page.')
@section('message', 'This area is restricted to authorised partnership staff. If you believe you should have access, please sign in with an authorised account or contact the coordination office.')

@section('secondary-action')
    <a href="{{ url('/login') }}" class="btn btn-outline">Staff login</a>
@endsection
