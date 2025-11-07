@php use Illuminate\Support\Facades\Session; @endphp
@php
  @endphp
  <!-- License Status Model -->
<div class="modal fade" id="licenseStatusModal" tabindex="-1" role="dialog" aria-labelledby="licenseStatusModalLabel"
     aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="licenseStatusModalLabel">License Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div>
          @if(Session::get('licenseStatus', false))
            You're running a genuine copy of {{config('variables.templateName')}}
          @else
            You are running an unlicensed copy {{config('variables.templateName')}}.
            <!-- License key enter and activation button -->
            <div class="row mt-3">
              <div class="form-group text-start">
                <label for="licenseKey">License Key</label>
                <input class="form-control" id="licenseKey" name="licenseKey" placeholder="XXXX-XXXX-XXXX-XXXX-XXXX" />
              </div>
            </div>

            <button class="btn btn-primary mt-3">Activate</button>

            <div class="divider my-6">
              <div class="divider-text">or</div>
            </div>

            <div class="d-flex justify-content-center">
              <a href="javascript:" class="btn btn-primary me-1_5">
                Activate with
                <img class="ms-2" src="{{asset('assets/img/envato.svg')}}" alt="Activate with envato" width="100">
              </a>
            </div>

          @endif
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ License Status Model -->
