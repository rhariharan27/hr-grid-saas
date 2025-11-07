<!-- Upgrade Modal -->
<div class="modal fade" id="upgradeModal" tabindex="-1" aria-labelledby="upgradeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="upgradeModalLabel">Upgrade Plan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><strong>Plan:</strong> <span id="upgradeModalPlanName"></span></p>
        <p><strong>Price Difference:</strong> {{$settings->currency_symbol}}<span id="upgradeModalDifference"></span>
        </p>
        <form method="POST">
          @csrf
          <input type="hidden" name="upgradeModal-planId" id="upgradeModal-planId">
          <div class="d-grid gap-2">
            @if ($gateways['paypal'])
              <button formaction="{{ route('paypal.paypalPaymentForUpgrade', ['gateway' => 'paypal']) }}"
                      class="btn btn-primary">Pay with PayPal
              </button>
            @endif
            @if ($gateways['razorpay'])
              <a href="#"
                 onclick="startRazorpayPaymentForUpgrade('{{ route('razorpay.razorPayPaymentForUpgrade') }}')"
                 class="btn btn-secondary">
                Pay with Razorpay
              </a>
            @endif @if($gateways['stripe'])
                <a href="#"
                   onclick="startStripePaymentForUpgrade('{{ route('stripeGateway.stripePaymentForUpgrade') }}')"
                   class="btn btn-success">
                  Pay with Stripe
                </a>
              @endif
            @if ($gateways['offline'])
              <hr>
              <p class="mt-3">{{ $gateways['offlineInstructions'] }}</p>
              <button
                formaction="{{ route('offlinePayment.payOfflineForUpgrade', ['gateway' => 'offline']) }}"
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
<!-- /Upgrade Modal -->
