@php
  use App\Services\Activation\IActivationService;use Illuminate\Support\Facades\Cache;use Illuminate\Support\Facades\Session;
  $containerFooter = (isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact') ? 'container-xxl' : 'container-fluid';
  $activationService = app()->make(IActivationService::class);
  $licenseStatus = Cache::store('file')->get('license_validity_' . config('app.url'));
@endphp

  <!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body">
        ©
        <script>document.write(new Date().getFullYear())</script>
        , made with ❤️ by <a href="{{ (!empty(config('variables.creatorUrl')) ? config('variables.creatorUrl') : '') }}"
                             target="_blank"
                             class="footer-link">{{ (!empty(config('variables.creatorName')) ? config('variables.creatorName') : '') }}</a>
      </div>
      <div class="d-none d-lg-inline-block">
        @if(config('custom.custom.activationService'))
          <a href="{{route('activation.index')}}"
             data-bs-toggle="tooltip"
             class="footer-link me-4"
             title="{{$licenseStatus ? "You're running a genuine copy." : "You are running an unlicensed copy."}}">
            <span class="footer-link-text">License Status</span>
            @if($licenseStatus)
              <i class="bx bxs-check-circle text-success ms-1"></i>
            @else
              <i class="bx bxs-x-circle text-danger ms-1"></i>
            @endif
          </a>
        @else
          <a href="{{config('variables.documentation')}}"
             target="_blank"
             class="footer-link me-4">
            <span class="footer-link-text">Documentation</span>
            <i class="bx bxs-book-open ms-1"></i>

          </a>
        @endif
      </div>
    </div>
  </div>
</footer>
<!--/ Footer-->
