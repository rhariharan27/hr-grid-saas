<!-- Initial Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentModalLabel">Choose Payment Method</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
          <p><strong>Plan:</strong> <span id="paymentModal-modalPlanName"></span></p>
          <p><strong>Base Price:</strong> {{$settings->currency_symbol}}<span id="paymentModal-modalPlanPrice"></span></p>
          <p><strong>Price Per User:</strong> {{$settings->currency_symbol}}<span id="paymentModal-modalPlanPerUserPrice"></span></p>
          <p><strong>Included Users:</strong> <span id="paymentModal-modalPlanUsers"></span></p>
        </div>
        <form id="paymentForm" method="POST">
          @csrf
          <input type="hidden" name="paymentModal-planId" id="paymentModal-planId">
          <div class="mb-3 col-6">
            <label for="users" class="form-label">Number of Additional Users</label>
            <div class="input-group">
              <button type="button" class="btn btn-outline-secondary" id="paymentModal-decreaseUsers">-</button>
              <input type="number" name="paymentModal-users" id="paymentModal-users" class="form-control text-center"
                     value="0" min="0">
              <button type="button" class="btn btn-outline-secondary" id="paymentModal-increaseUsers">+</button>
            </div>
          </div>
          <p class="text-muted text-end">
            <strong>Total Price: {{$settings->currency_symbol}}<span id="paymentModal-totalPrice"></span></strong>
          </p>
          <div class="d-grid gap-2">
            @if ($gateways['paypal'])
              <button type="submit" onclick="startPaypalPayment('{{ route('paypal.paypalPayment') }}')"
                      class="btn btn-primary">Pay
                with PayPal
              </button>
            @endif
            @if ($gateways['razorpay'])
              <a href="#"
                 onclick="startRazorpayPayment('{{ route('razorpay.razorPayPayment') }}')"
                 class="btn btn-secondary">
                Pay with Razorpay
              </a>
            @endif
            @if ($gateways['offline'])
              <hr>
              <p class="mt-3">{{$gateways['offlineInstructions']}}</p>
              <button formaction="{{ route('offlinePayment.create', ['gateway' => 'offline', 'type'=> 'plan']) }}"
                      class="btn btn-info">
                Pay Offline
              </button>
            @endif
            @if($gateways['stripe'])
              <hr>
              <a href="#" onclick="startStripePayment('{{ route('stripeGateway.stripePayment') }}')"
                      class="btn btn-success">Pay with Stripe
              </a>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- /Initial Payment Modal -->
