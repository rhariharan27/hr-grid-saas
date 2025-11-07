@php
  use App\Services\AddonService\IAddonService;
  $addonService = app(IAddonService::class);
  $isLandingPageEnabled = $addonService->isSAAddonEnabled(ModuleConstants::LANDING_PAGE);
@endphp
  <!-- Footer: Start -->
<footer class="landing-footer bg-body footer-text">
  <div class="footer-top position-relative overflow-hidden z-1">
    <img src="{{asset('assets/img/front-pages/backgrounds/footer-bg.png')}}" alt="footer bg"
         class="footer-bg banner-bg-img z-n1" />
    <div class="container">
      <div class="row gx-0 gy-6 g-lg-10">
        <div class="col-lg-5">
          <a href="javascript:" class="app-brand-link mb-6">
            <span class="app-brand-logo demo">
              <img src="{{asset('assets/img/logo.png')}}" alt="Logo" width="27">
            </span>
            <span
              class="app-brand-text demo text-white fw-bold ms-2 ps-1">{{config('variables.templateFullName')}}</span>
          </a>
          <p class="footer-text footer-logo-description mb-6">
            @if($isLandingPageEnabled)
              {{$landingSettings->footer_description}}
            @else
              {{config('variables.templateDescription')}}
            @endif
          </p>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <h6 class="footer-title mb-6">Links</h6>
          <ul class="list-unstyled">
            <li class="mb-4">
              <a href="{{$isLandingPageEnabled ? route('landingPage.privacyPolicy') : url('')}}" class="footer-link">Privacy
                Policy</a>
            </li>
            <li class="mb-4">
              <a href="{{$isLandingPageEnabled ?route('landingPage.termsOfService') : url('')}}" class="footer-link">Terms
                of Service</a>
            </li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-4">
          @if($isLandingPageEnabled)
            <h6 class="footer-title mb-6">Download our app</h6>
            <a href="{{$landingSettings->app_store_link}}" class="d-block mb-4"><img
                src="{{asset('assets/img/front-pages/landing-page/apple-icon.png')}}" alt="apple icon" /></a>
            <a href="{{$landingSettings->play_store_link}}" class="d-block"><img
                src="{{asset('assets/img/front-pages/landing-page/google-play-icon.png')}}"
                alt="google play icon" /></a>
          @endif
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom py-3 py-md-5">
    <div class="container d-flex flex-wrap justify-content-between flex-md-row flex-column text-center text-md-start">
      <div class="mb-2 mb-md-0">
        @if(!isset($landingSettings))
          <span class="footer-bottom-text">©
          <script>
          document.write(new Date().getFullYear())
          </script>
        </span>
          <span class="footer-bottom-text"> Made with ❤️ by <a href="javascript:" target="_blank" class="text-white">{{config('variables.creatorName')}},</a> All rights reserved.</span>
        @else
          <span class="footer-bottom-text">
           {{$landingSettings->footer_text}}
          </span>
        @endif
      </div>
    </div>
  </div>
</footer>
<!-- Footer: End -->
