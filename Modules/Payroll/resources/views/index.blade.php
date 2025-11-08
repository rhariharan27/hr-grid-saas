@php use Carbon\Carbon; @endphp
@extends('layouts.layoutMaster')

@section('title', __('Payroll Management'))

@section('vendor-style')
  @vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/select2/select2.scss',
  ])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/select2/select2.js',
  ])
@endsection

@section('page-script')
  @vite(['resources/assets/js/app/payroll-index.js'])
  <script>
    var csrfToken = '{{ csrf_token() }}'
    var payrollGenerateRoute = '{{ route('payroll.generate') }}'
  </script>
@endsection

@section('content')
  <div class="row">
    <div class="col">
      <h4>@lang('Payroll Management')</h4>
    </div>
    <div class="col">
      <div class="float-end">
        @if(env('APP_DEMO'))
          <a class="btn btn-primary" href="{{route('payroll.testGen')}}"> Test Gen</a>
        @endif
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
          @lang('Generate Payroll')
        </button>
      </div>
    </div>
  </div>
  <!-- Filters Section -->
  <div class="row mb-4">
    <!-- Employee Filter -->
    <div class="col-md-3 mb-3">
      <label for="employeeFilter" class="form-label">Filter by employee</label>
      <select id="employeeFilter" name="employeeFilter" class="form-select select2 filter-input">
        <option value="" selected>All Employees</option>
        @foreach($employees as $employee)
          <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
        @endforeach
      </select>
    </div>

    <!-- Date Filter -->
    <div class="col-md-3 mb-3">
      <label for="dateFilter" class="form-label">Filter by Period</label>
      <input type="month" id="dateFilter" name="dateFilter" class="form-control filter-input"
             value="{{ date('Y-m') }}">
    </div>

    <!-- Status Filter -->
    <div class="col-md-3 mb-3">
      <label for="statusFilter" class="form-label">Filter by Status</label>
      <select id="statusFilter" name="statusFilter" class="form-select select2 filter-input">
        <option value="" selected>All Status</option>
        <option value="pending">Pending</option>
        <option value="completed">Completed</option>
        <option value="paid">Paid</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
  </div>

  <!-- Payroll Table -->
  <div class="card">
    <div class="card-datatable table-responsive">
      <table class="table border-top datatables-payroll">
        <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Period</th>
          <th>Basic Salary</th>
          <th>Gross Salary</th>
          <th>Net Salary</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>

  {{-- NEW: Generate Payroll Modal --}}
  <div class="modal fade" id="generatePayrollModal" tabindex="-1" aria-labelledby="generatePayrollModalLabel"
       aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="generatePayrollModalLabel">Generate Payroll for Period</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="generatePayrollForm" onsubmit="return false;">
          @csrf
          <div class="modal-body">
            <div class="mb-3">
              <label for="payrollPeriod" class="form-label">Select Period (Month/Year) <span
                  class="text-danger">*</span></label>
              {{-- Input type month gives YYYY-MM format --}}
              <input type="month" class="form-control" id="payrollPeriod" name="period" required
                     value="{{ Carbon::now()->subMonth()->format('Y-m') }}"> {{-- Default to previous month --}}
              <div class="invalid-feedback"></div>
              <small class="text-muted">Payroll will be generated based on attendance and settings for the selected
                month.</small>
            </div>
            <div class="mt-3 general-error-message text-danger small"></div> {{-- For general errors --}}
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitGenerateBtn">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"
                    style="display: none;"></span>
              Generate
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  {{-- End Generate Payroll Modal --}}
@endsection
