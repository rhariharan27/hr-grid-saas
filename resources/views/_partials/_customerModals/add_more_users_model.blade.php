<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">Add Users to Subscription</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="POST">
          @csrf
          <div class="mb-3">
            <div class="input-group">
              <button type="button" class="btn btn-outline-secondary" id="decreaseUsers">-</button>
              <input type="number" name="addUserModal-users" id="addUserModal-users" class="form-control text-center"
                     value="1" min="0">
              <button type="button" class="btn btn-outline-secondary" id="increaseUsers">+</button>
            </div>
          </div>
          <div class="mb-3">
            <p><strong>Per User Price:</strong> {{$settings->currency_symbol}}{{ $activePlan->per_user_price }}</p>
            <p><strong>Total Additional Cost:</strong> {{$settings->currency_symbol}}<span
                id="addUserModal-totalCost">0</span> / {{ucfirst($activePlan->duration_type->value)}}
            </p>
            <p><strong>Amount to be paid now:</strong> {{$settings->currency_symbol}}<span
                id="addUserModal-amountToBePaid">0</span></p>
          </div>
          <div class="d-grid gap-2">
            @if ($gateways['paypal'])
              <button formaction="{{ route('paypal.paypalPaymentForAddUser', ['gateway' => 'paypal']) }}"
                      class="btn btn-primary">Pay with PayPal
              </button>
            @endif
            @if ($gateways['razorpay'])
              <a href="#"
                 onclick="startRazorpayPaymentForUserAdd('{{ route('razorpay.razorPayPaymentForAddUser') }}')"
                 class="btn btn-secondary">
                Pay with Razorpay
              </a>
            @endif
              @if($gateways['stripe'])
                <a href="#" onclick="startStripePaymentForUserAdd('{{ route('stripeGateway.stripePaymentForAddUser') }}')"
                   class="btn btn-success">Pay with Stripe
                </a>
              @endif
            @if ($gateways['offline'])
                <hr>
              <p class="mt-3">{{ $gateways['offlineInstructions'] }}</p>
              <button formaction="{{ route('offlinePayment.payOfflineForUserAdd', ['gateway' => 'offline']) }}"
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
<!-- /Add User Modal -->
