{{-- resources/views/_partials/_alerts.blade.php --}}

{{-- Session Success Message --}}
@if (session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
      <i class="bx bx-check-circle me-2"></i>
      <span>{{ session('success') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

{{-- Session Error Message --}}
@if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
      <i class="bx bx-error-circle me-2"></i>
      <span>{{ session('error') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

{{-- Session Warning Message --}}
@if (session('warning'))
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
      <i class="bx bx-error-alt me-2"></i>
      <span>{{ session('warning') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

{{-- Session Info Message --}}
@if (session('info'))
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
      <i class="bx bx-info-circle me-2"></i>
      <span>{{ session('info') }}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

{{-- Validation Errors --}}
@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h6 class="alert-heading mb-1"><i class="bx bx-error me-2"></i>Validation Errors:</h6>
    <ul class="mb-0 ps-4">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif
