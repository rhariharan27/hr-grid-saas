@php use App\Enums\ExpenseRequestStatus; @endphp
@extends('layouts/layoutMaster')

@section('title', __('Expense Requests'))

<!-- Vendor Styles -->
@section('vendor-style')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
 'resources/assets/vendor/libs/@form-validation/form-validation.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss',
   'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
    'resources/assets/vendor/libs/select2/select2.scss',])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
 'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
   'resources/assets/vendor/libs/@form-validation/auto-focus.js',
   'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
  <script>
    const currencySymbol = @json($settings->currency_symbol);
  </script>
  @vite(['resources/assets/js/app/expense-requests-index.js'])
@endsection


@section('content')
  <div class="row">
    <div class="col">
      <h4>@lang('Expense Requests')</h4>
    </div>
  </div>
  <!-- Filters Section -->
  <div class="row mb-4">
    <!-- Employee Filter -->
    <div class="col-md-3 mb-3">
      <label for="employeeFilter" class="form-label">Filter by employee</label>
      <select id="employeeFilter" name="employeeFilter" class="form-select select2">
        <option value="" selected>All Employees</option>
        @foreach($employees as $employee)
          <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>
        @endforeach
      </select>
    </div>

    <!--Date Filter -->
    <div class="col-md-3 mb-3">
      <label for="dateFilter" class="form-label">Filter by date</label>
      <input type="date" id="dateFilter" name="dateFilter" class="form-control">
    </div>

    <!-- Expense Type filter -->
    <div class="col-md-3 mb-3">
      <label for="expenseTypeFilter" class="form-label">Filter by expense type</label>
      <select id="expenseTypeFilter" name="expenseTypeFilter" class="form-select select2">
        <option value="" selected>All Expense Types</option>
        @foreach($expenseTypes as $expenseType)
          <option value="{{ $expenseType->id }}">{{ $expenseType->name }}</option>
        @endforeach
      </select>
    </div>

    <!-- Status Filter -->
    <div class="col-md-3 mb-3">
      <label for="statusFilter" class="form-label">Filter by status</label>
      <select id="statusFilter" name="statusFilter" class="form-select select2">
        <option value="" selected>All Statuses</option>
        @foreach(ExpenseRequestStatus::cases() as $gender)
          <option value="{{ $gender->value }}">{{ $gender->name }}</option>
        @endforeach
      </select>
    </div>
  </div>
  <!-- Leave Requests table card -->
  <div class="card">
    <div class="card-datatable table-responsive">
      <table class="datatables-expenseRequests table border-top">
        <thead>
        <tr>
          <th>@lang('')</th>
          <th>@lang('Id')</th>
          <th>@lang('User')</th>
          <th>@lang('Expense Type')</th>
          <th>@lang('Expense Date')</th>
          <th>@lang('Amount')</th>
          <th>@lang('Status')</th>
          <th>@lang('Image')</th>
          <th>@lang('Actions')</th>
        </tr>
        </thead>
      </table>
    </div>
  </div>
  @include('_partials._modals.expense.expense_request_details')
@endsection


