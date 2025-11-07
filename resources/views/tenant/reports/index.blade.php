@php
  use App\Services\AddonService\IAddonService;$title = 'Reports';

  $addonService = app(IAddonService::class);

$reports = ['Attendance'];

if($addonService->isAddonEnabled(ModuleConstants::LEAVE_MANAGEMENT,true)){
    $reports[] = 'Leave';
}

if($addonService->isAddonEnabled(ModuleConstants::EXPENSE_MANAGEMENT,true)){
    $reports[] = 'Expense';
}

if($addonService->isAddonEnabled(ModuleConstants::CLIENT_VISIT,true)){
    $reports[] = 'Visit';
}

if($addonService->isAddonEnabled(ModuleConstants::PRODUCT_ORDER)){
    $reports[] = 'ProductOrder';
}


@endphp

@extends('layouts/layoutMaster')

@section('title', __($title))

@section('content')
  <div class="row mb-4">
    <div class="col text-start">
      <h4 class="mt-4">{{$title}}</h4>
    </div>
  </div>

  <div class="row justify-content-start">
    @foreach($reports as $report)
      <div class="col-sm-6 col-lg-4 col-xl-3 mt-3">
        <div class="card h-100">
          <div class="card-header text-start">
            <h5 class="card-title mb-0">{{ $report }} Report</h5>
          </div>
          <div class="card-body d-flex flex-column">
            <form action="{{ route('report.get'. $report . 'Report') }}" method="post" class="mt-auto">
              @csrf
              <div class="form-group mb-3">
                <label for="period">Period</label>
                <input type="month" class="form-control" id="period" name="period" required/>
              </div>
              <button type="submit" class="btn btn-primary btn-block mt-4">Generate Report</button>
            </form>
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endsection
