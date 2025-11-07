@php
  $modules = [
  [
      'id' => 'holidays',
      'title' => 'Holidays',
      'description' => 'Import / Export holiday masters.',
      'sampleUrl' => '/samples/holidays.csv',
      'importRoute' => 'dataImportExport.import',
      'exportRoute' => 'dataImportExport.export',
  ],
  [
      'id' => 'attendances',
      'title' => 'Attendances',
      'description' => 'Import / Export attendance records.',
      'sampleUrl' => '/samples/attendance.csv',
      'importRoute' => 'dataImportExport.import',
      'exportRoute' => 'dataImportExport.export',
  ],
  [
      'id' => 'leave-types',
      'title' => 'Leave Type',
      'description' => 'Import / Export leave type masters.',
      'sampleUrl' => '/samples/leave-types.csv',
      'importRoute' => 'dataImportExport.import',
      'exportRoute' => 'dataImportExport.export',
  ],
  [
  'id'=>'expense-types',
  'title'=>'Expense Types',
  'description'=>'Import / Export expense type masters.',
  'sampleUrl'=>'/samples/expense-types.csv',
  'importRoute'=>'dataImportExport.import',
  'exportRoute'=> 'dataImportExport.export'
],
[
'id'=> 'teams',
  'title'=> 'Teams',
  'description'=> 'Import / Export team masters.',
  'sampleUrl'=> '/samples/teams.csv',
  'importRoute'=> 'dataImportExport.import',
  'exportRoute'=> 'dataImportExport.export',
],
[
      'id' => 'shifts',
      'title' => 'Shifts',
      'description' => 'Import / Export shift masters.',
      'sampleUrl' => '/samples/shifts.csv',
      'importRoute' => 'dataImportExport.import',
      'exportRoute' => 'dataImportExport.export',
],
[
  'id'=>'departments',
  'title'=>'Departments',
  'description'=>'Import / Export department masters.',
  'sampleUrl'=>'/samples/departments.csv',
  'importRoute'=>'dataImportExport.import',
  'exportRoute'=>'dataImportExport.export',
],
[
  'id'=>'designations',
  'title'=>'Designations',
  'description'=>'Import / Export designation masters.',
  'sampleUrl'=>'/samples/designations.csv',
  'importRoute'=>'dataImportExport.import',
  'exportRoute'=>'dataImportExport.export',
],[
  'id'=>'clients',
  'title'=>'Clients',
  'description'=>'Import / Export client masters.',
  'sampleUrl'=>'/samples/clients.csv',
  'importRoute'=>'dataImportExport.import',
  'exportRoute'=>'dataImportExport.export',
],
[
      'id'=>'employees',
      'title'=>'Employees',
      'description'=>'Import / Export employee masters.',
      'sampleUrl'=>'/samples/employees.csv',
      'importRoute'=>'dataImportExport.import',
      'exportRoute'=>'dataImportExport.export',
]



];


//Product Order
   $modules[] = [
          'id' => 'categories',
          'title' => 'Categories',
          'description' => 'Import / Export category masters.',
          'sampleUrl' => '/samples/product-category.csv',
          'importRoute' => 'dataImportExport.import',
          'exportRoute' => 'dataImportExport.export',
      ];

      $modules[] = [
          'id' => 'products',
          'title' => 'Products',
          'description' => 'Import / Export product masters.',
          'sampleUrl' => '/samples/products.csv',
          'importRoute' => 'dataImportExport.import',
          'exportRoute' => 'dataImportExport.export',
      ];
@endphp
@php
  $title = 'Data Import / Export';
@endphp
@section('title', $title)

@extends('layouts/layoutMaster')

@section('content')
  <div class="row mb-3">
    <div class="col">
      <div class="float-start">
        <h4 class="mt-2">@yield('title')</h4>
      </div>
    </div>
    <div class="col"></div>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach ($errors->all() as $error)
          <li class="text-white">{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-4 g-3 mb-9">
    <!-- Dynamic Cards -->
    @foreach ($modules as $module)
      <div class="col">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <h4 class="mb-2 line-clamp-1 lh-sm me-5">{{ $module['title'] }}</h4>
            </div>

            <p class="mb-0 fw-bold fs--1">{{ $module['description'] }}</p>

            <div class="d-flex justify-content-between align-items-center mt-3">
              <div>
                <a href="{{ $module['sampleUrl'] }}" class="text-decoration-none">
                  <i class="fa fa-download me-2"></i>Download Sample
                </a>
              </div>

              <div class="d-flex">
                <button class="btn btn-phoenix-primary me-2" data-bs-toggle="tooltip"
                        data-bs-placement="top" title="Import"
                        onclick="openImportModal('{{ $module['id'] }}')">
                  <i class="fa fa-upload"></i>
                </button>
                <a href="{{ route($module['exportRoute'], $module['id']) }}"
                   class="btn btn-phoenix-primary" data-bs-toggle="tooltip"
                   data-bs-placement="top" title="Export">
                  <i class="fa-solid fa-download"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

    @endforeach
  </div>

  <!-- Import Modals -->
  @foreach ($modules as $module)
    <div class="modal fade" id="importModal-{{ $module['id'] }}" tabindex="-1" role="dialog"
         aria-labelledby="modalLabel-{{ $module['id'] }}">
      <div class="modal-dialog modal-l" role="document">
        <div class="modal-content p-3">
          <h4 class="modal-title" id="modalLabel-{{ $module['id'] }}">Import {{ $module['title'] }}</h4>
          <div class="modal-body">
            <form method="POST" enctype="multipart/form-data"
                  action="{{ route($module['importRoute'],$module['id']) }}">
              @csrf
              <input type="hidden" name="type" id="type" value="{{ $module['id'] }}">
              <div class="form-group">
                <label for="file">Choose File:</label>
                <input type="file" class="form-control" id="file" name="file" accept=".csv" required/>
              </div>
              <button type="submit" class="btn btn-primary mt-3">Import</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endforeach
@endsection

@section('page-script')
  <script>
    function openImportModal(moduleId) {
      const modalId = `#importModal-${moduleId}`;
      $(modalId).modal('toggle');
    }
  </script>
@endsection
