@extends('layouts/layoutMaster')

@section('title', __('Clients'))

<!-- Vendor Styles -->
@section('vendor-style')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
    'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
  ])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  ])
@endsection

@section('content')
  <div class="row mb-3">
    <div class="col">
      <div class="float-start">
        <h4 class="mt-2">{{__('Clients')}}</h4>
      </div>
    </div>
    <div class="col">
      <div class="float-end">
        <a href="{{ route('client.create') }}" class="btn btn-primary">Create new</a>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="datatable" class="table table-striped">
          <thead>
          <tr>
            <th>Sl.No</th>
            <th>Name</th>
            <th>Phone Number</th>
            <th>Email</th>
            <th>Address</th>
            <th>City</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
          </thead>
          <tbody>
          @foreach ($clients as $client)
            <tr>
              <td class="ps-2">
                {{ $loop->iteration }}
              </td>
              <td>{{ $client->name }}</td>
              <td>{{ $client->phone }}</td>
              <td>{{ $client->email }}</td>
              <td>{{ $client->address }}</td>
              <td>{{ $client->city ?? 'N/A' }}</td>
              <td>
                <div class="d-flex justify-content-left">
                  <label class="switch mb-0">
                    <input
                      type="checkbox"
                      {{$client->status == 'active' ? 'checked' : ''}}
                      onchange="changeStatus({{ $client->id }})"
                      class="switch-input status-toggle"/>
                    <span class="switch-toggle-slider">
              <span class="switch-on"><i class="bx bx-check"></i></span>
              <span class="switch-off"><i class="bx bx-x"></i></span>
              </span>
                  </label>
                </div>
              </td>
              <td>
                <div class="d-flex align-items-center">
                  <a href="{{ route('client.show', $client->id) }}" class="btn btn-icon btn-sm me-2">
                    <i class="fa fa-eye"></i>
                  </a>
                  <a href="{{ route('client.edit', $client->id) }}" class="btn btn-icon btn-sm me-2">
                    <i class="fa fa-edit"></i>
                  </a>
                  <form action="{{ route('client.destroy', $client->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-icon btn-sm"
                            onclick="return confirm('Are you sure you want to delete this client?')">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form>
                </div>
              </td>

            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@section('page-script')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <script>

    $(function () {
      $('#datatable').dataTable();
    });

    function changeStatus(id) {
      $.ajax({
        'csrf-token': '{{csrf_token()}}',
        url: "{{route('client.changeStatus')}}",
        type: 'POST',
        dataType: 'json',
        data: {
          id: id,
          _token: "{{ csrf_token() }}"
        },
        success: function (data) {
          console.log(data);
          showSuccessToast(data);
        },
        error: function (data) {
          console.log(data);
        }
      });
    }
  </script>
@endsection

