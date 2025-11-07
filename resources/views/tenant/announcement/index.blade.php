@php

  @endphp

@extends('layouts/layoutMaster')

@section('title', 'Announcements')

@section('content')
  <h4>Announcements</h4>
  <p>For more layout options refer <a
      href="{{ config('variables.documentation') ? config('variables.documentation').'/laravel-introduction.html' : '#' }}"
      target="_blank" rel="noopener noreferrer">documentation</a>.</p>
  <a class="btn btn-primary" href="{{ route('announcements.create') }}">Create Announcement</a>
  <a class="btn btn-primary" href="{{ route('notifications.index') }}">Notifications Json</a>
@endsection
