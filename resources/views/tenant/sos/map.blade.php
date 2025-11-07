@php
  $title = 'SOS Requests Map';
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
  <!-- Summary Cards -->
  <div class="row mb-4">
    <div class="col-md-4">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5>Total Requests</h5>
          <h3 class="fw-bold">{{ $totalRequests }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5>Pending Requests</h5>
          <h3 class="fw-bold text-warning">{{ $pendingRequests }}</h3>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card text-center shadow-sm">
        <div class="card-body">
          <h5>Resolved Requests</h5>
          <h3 class="fw-bold text-success">{{ $resolvedRequests }}</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- SOS Map -->
  <div class="card shadow-sm">
    <div id="map" style="height: 75vh;"></div>
  </div>
@endsection

@section('page-script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&v=weekly"
          async defer></script>

  <script>
    let map, markers = [];

    function initMap() {
      const centerLat = parseFloat('{{ $settings->center_latitude }}');
      const centerLng = parseFloat('{{ $settings->center_longitude }}');
      const zoomLevel = parseInt('{{ $settings->map_zoom_level }}');

      map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: centerLat, lng: centerLng },
        zoom: zoomLevel,
        gestureHandling: 'greedy',
        streetViewControl: false
      });

      fetchSOSRequests();
    }

    function fetchSOSRequests() {
      $.ajax({
        url: "{{ route('sos.fetch') }}",
        type: 'GET',
        success: (response) => {
          clearMarkers();
          response.forEach(log => addMarker(log));
        },
        error: (e) => console.error(e)
      });
    }

    function addMarker(log) {
      const marker = new google.maps.Marker({
        position: { lat: parseFloat(log.latitude), lng: parseFloat(log.longitude) },
        map,
        title: log.name,
        icon: {
          url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
        },
        animation: google.maps.Animation.BOUNCE
      });

      const infoWindow = new google.maps.InfoWindow({
        content: `
          <div>
            <h6>${log.name}</h6>
            <p><strong>Address:</strong> ${log.address || 'N/A'}</p>
            <p><strong>Notes:</strong> ${log.notes || 'N/A'}</p>
            <p><strong>Created At:</strong> ${log.created_at}</p>
            <button class="btn btn-sm btn-success mt-2" onclick="resolveSOS(${log.id})">Mark as Resolved</button>
          </div>
        `
      });

      marker.addListener('click', () => {
        infoWindow.open(map, marker);
      });

      markers.push(marker);
    }

    function resolveSOS(id) {
      $.ajax({
        url: `/sos/resolve/${id}`,
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        success: (response) => {
          if (response.success) {
            alert(response.message);
            fetchSOSRequests();
          }
        },
        error: (e) => console.error(e)
      });
    }

    function clearMarkers() {
      markers.forEach(marker => marker.setMap(null));
      markers = [];
    }
  </script>

  <style>
    .gm-style .marker-animation {
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0% {
        transform: scale(0.9);
        opacity: 0.7;
      }
      50% {
        transform: scale(1.1);
        opacity: 1;
      }
      100% {
        transform: scale(0.9);
        opacity: 0.7;
      }
    }
  </style>
@endsection
