@extends('layouts/layoutMaster')

@section('title', 'Super Admin Dashboard')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/apex-charts/apex-charts.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/apex-charts/apexcharts.js'])
@endsection

@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const orderHistoryChartEl = document.querySelector('#orderHistoryChart');
      const orderHistoryData = @json($orderHistory);
      console.log(orderHistoryData);

      if (orderHistoryChartEl) {
        const options = {
          chart: {
            type: 'line',
            height: 350
          },
          series: [{
            name: 'Total Amount',
            data: orderHistoryData.map(item => item.total)
          }],
          xaxis: {
            categories: orderHistoryData.map(item => `Month ${item.month}`)
          },
          colors: ['#4CAF50']
        };

        const chart = new ApexCharts(orderHistoryChartEl, options);
        chart.render();
      }
    });
  </script>
@endsection

@section('content')

  <div class="row">
    <!-- Overview Cards -->
    <div class="col-sm-6 col-xl-3 mb-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5>Total Orders</h5>
          <h2>{{ $totalOrders }}</h2>
          <small class="text-success">{{ $completedOrders }} Completed</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5>Pending Requests</h5>
          <h2>{{ $pendingRequests }}</h2>
          <small class="text-warning">Action Required</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5>Active Domains</h5>
          <h2>{{ $activeDomains }}</h2>
          <small class="text-primary">Operational Domains</small>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3 mb-4">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h5>New Customers</h5>
          <h2>{{ $newCustomers }}</h2>
          <small class="text-info">This Month</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Order History Graph -->
  <div class="row">
    <div class="col-lg-8 mb-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">Order History</h5>
          <p class="mb-0 text-muted">Monthly Overview</p>
        </div>
        <div class="card-body">
          <div id="orderHistoryChart"></div>
        </div>
      </div>
    </div>

    <!-- Offline Requests -->
    <div class="col-lg-4 mb-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">Offline Requests</h5>
          <p class="mb-0 text-muted">Pending Approvals</p>
        </div>
        <div class="card-body overflow-auto" style="max-height: 400px;">
          @if($offlineRequests->count() > 0)
            <ul class="list-group list-group-flush">
              @foreach($offlineRequests as $request)
                <li class="list-group-item">
                  <strong>{{ $request->user->name }}</strong> - {{ $request->status }}
                  <span class="text-muted d-block">{{ $request->created_at->format('d M, Y') }}</span>
                </li>
              @endforeach
            </ul>
          @else
            <div class="card-body text-center">
              <h5 class="fw-bold">No Pending Requests</h5>
              <p class="text-muted">All offline requests are up to date.</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Domain Requests and Recent Customers -->
  <div class="row">
    <!-- Domain Requests -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">Domain Requests</h5>
          <p class="mb-0 text-muted">Pending Domains</p>
        </div>
        <div class="card-body overflow-auto" style="max-height: 400px;">
          @if($domainRequests->count() > 0)
            <ul class="list-group list-group-flush">
              @foreach($domainRequests as $request)
                <li class="list-group-item">
                  @include('_partials._profile-avatar', [
            'user' => $request->user,
          ])
                  <strong>{{ $request->name }}</strong> - {{ $request->status }}
                  <span class="text-muted d-block">{{ $request->created_at->format('d M, Y') }}</span>
                </li>
              @endforeach

            </ul>
          @else
            <div class="card-body text-center">
              <h5 class="fw-bold">No Pending Requests</h5>
              <p class="text-muted">All domain requests are up to date.</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Recent Customers -->
    <div class="col-lg-6 mb-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0">Recent Customers</h5>
          <p class="mb-0 text-muted">Newest Signups</p>
        </div>
        <div class="card-body overflow-auto" style="max-height: 400px;">
          @if($recentCustomers->count() > 0)
            <ul class="list-group list-group-flush">
              @foreach($recentCustomers as $customer)
                <li class="list-group-item">
                  @include('_partials._profile-avatar',[
    'user' => $customer
    ])
                  <span class="text-muted d-block">{{ $customer->created_at->format('d M, Y') }}</span>
                </li>
              @endforeach
            </ul>
          @else
            <div class="card-body text-center">
              <h5 class="fw-bold">No New Customers</h5>
              <p class="text-muted">No new customers have signed up recently.</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

@endsection
