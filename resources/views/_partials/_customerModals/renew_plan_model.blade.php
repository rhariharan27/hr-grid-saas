<!-- Renew Modal -->
<div class="modal fade" id="renewModal" tabindex="-1" aria-labelledby="renewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="renewModalLabel">Renew Subscription</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          @csrf
          <div class="mb-3">
            <p><strong>Plan:</strong> {{$activePlan->name}}</p>
            <p><strong>Base Price</strong> {{$settings->currency_symbol}}{{ $activePlan->base_price }}</p>
            <p><strong>Per User Price:</strong> {{$settings->currency_symbol}}{{ $activePlan->per_user_price }}</p>
            <p><strong>Duration</strong> {{$activePlan->duration }} {{ucfirst($activePlan->duration_type->value)}}</p>
            <p><strong>Additional Users Count:</strong> {{$subscription->additional_users}}</p>
            <p><strong>Amount to be paid:</strong>
              <span>{{$settings->currency_symbol}}{{$activePlan->base_price + ($activePlan->per_user_price * $subscription->additional_users)}}</span>
            </p>
          </div>
          <div class="d-grid gap-2">
            @if ($gateways['paypal'])
              <button formaction="{{ route('paypal.paypalPaymentForRenewal', ['gateway' => 'paypal']) }}"
                      class="btn btn-primary">Pay with PayPal
              </button>
            @endif
            @if ($gateways['razorpay'])
              <a href="#"
                 onclick="startRazorpayPaymentForRenewal('{{ route('razorpay.razorPayPaymentForRenewal') }}')"
                 class="btn btn-secondary">
                Pay with Razorpay
              </a>
            @endif
              @if($gateways['stripe'])
                <a href="#"
                   onclick="startStripePaymentForRenewal('{{ route('stripeGateway.stripePaymentForRenewal') }}')"
                   class="btn btn-success">
                  Pay with Stripe
                </a>
              @endif
            @if ($gateways['offline'])
              <hr>
              <p class="mt-3">{{ $gateways['offlineInstructions'] }}</p>
              <button formaction="{{ route('offlinePayment.payOfflineForRenewal', ['gateway' => 'offline']) }}"
                      class="btn btn-info">
                Pay Offline
              </button>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- /Renew Modal -->
