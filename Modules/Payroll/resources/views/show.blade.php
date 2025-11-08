@php
  use Modules\Payroll\Enums\PayrollRecordStatus; // Import Enum
  $currency = $settings->currency_symbol ?? '$';
  // Determine if adjustments or status changes are allowed based on current status
  $canModifyAdjustments = in_array($payroll->status, [PayrollRecordStatus::PENDING, PayrollRecordStatus::PROCESSED]);
  $canMarkCompleted = in_array($payroll->status, [PayrollRecordStatus::PENDING, PayrollRecordStatus::PROCESSED]);
  $canMarkPaid = $payroll->status === PayrollRecordStatus::COMPLETED;
  $canCancel = !in_array($payroll->status, [PayrollRecordStatus::PAID, PayrollRecordStatus::CANCELLED]);
@endphp

@extends('layouts.layoutMaster')

@section('title', __('Payroll Details') . ' - ' . $payroll->period . ' (' . $payroll->user->getFullName() . ')')


@section('vendor-style')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
        'resources/assets/vendor/libs/select2/select2.js', // For modal dropdown
      'resources/assets/vendor/libs/cleavejs/cleave.js', // For amount input
])
@endsection

@section('page-script')
  <style>
    @media print {
      body * {
        visibility: hidden;
      }

      #printableArea, #printableArea * {
        visibility: visible;
      }

      #printableArea {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
      }

      .print-hidden {
        display: none !important;
      }
    }
  </style>

  <script>
    const payrollRecordId = {{ $payroll->id }};
    // URLs for JS interactions
    const storeManualAdjustmentUrl = "{{ route('payroll.records.adjustments.storeManual', $payroll->id) }}"
    const adjustmentsLogBaseUrl = "{{ url('lms/enrollments') }}" // Base URL for deleting adjustment logs - NEEDS CORRECTION
    // Correct base URL assuming route setup: DELETE /payroll/adjustments/log/{log}
    const adjustmentsLogDeleteUrlTemplate = "{{ route('payroll.adjustments.destroyManual', ['log' => ':logId']) }}" // Use template
    const markCompletedUrl = "{{ route('payroll.records.markCompleted', $payroll->id) }}"
    const markPaidUrl = "{{ route('payroll.records.markPaid', $payroll->id) }}"
    const cancelRecordUrl = "{{ route('payroll.records.cancel', $payroll->id) }}"
    const csrfToken = "{{ csrf_token() }}"

    function printPayroll() {
      window.print()
    }

    function sendPayroll() {
      Swal.fire({
        title: '@lang("Send Payroll Details")',
        text: '@lang("Are you sure you want to send payroll details to the user?")',
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: '@lang("Yes, send it!")',
        cancelButtonText: '@lang("Cancel")',
        customClass: {
          confirmButton: "btn btn-primary me-2",
          cancelButton: "btn btn-label-secondary"
        }
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire({
            title: '@lang("Sent!")',
            text: '@lang("Payroll details have been sent successfully.")',
            icon: "success",
            customClass: {
              confirmButton: "btn btn-success"
            }
          })
        }
      })
    }
  </script>
  @vite(['resources/assets/js/app/payroll-show.js'])
@endsection
@section('content')
  <div class="container-fluid flex-grow-1 container-p-y"> {{-- Add container for padding --}}
    <h4 class="py-3 mb-4">
      <span class="text-muted fw-light"><a href="{{ route('payroll.index') }}">Payroll</a> /</span> Details
      for {{ $payroll->period }}
    </h4>
    <div class="row">
      <!-- Payroll Details (8 Columns) -->
      <div class="col-md-8" id="printableArea">
        <div class="card shadow-sm">
          <!-- Card Header with Actions -->
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bx bx-receipt me-2 text-primary"></i>@lang('Payroll Details')
              for {{ $payroll->user->getFullName() }}</h5>
            <div>
              {{-- Payslip Download --}}
              @if($payroll->payslip)
                <a href="{{ route('tenant.payroll.payslip.pdf', $payroll->payslip->id) }}"
                   class="btn btn-sm btn-outline-secondary me-2" target="_blank">
                  <i class="bx bx-download"></i> @lang('Download Payslip')
                </a>
              @else
                <span class="text-muted small me-2">(Payslip not generated yet)</span>
              @endif
              {{-- Print Button (Optional) --}}
              {{-- <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bx bx-printer"></i> @lang('Print')</button> --}}
            </div>
          </div>

          <!-- Card Body -->
          <div class="card-body">
            <!-- User Info Section -->
            <h6 class="fw-bold text-primary mb-3"><i class="bx bx-user me-1"></i>@lang('User Information')</h6>
            <ul class="list-unstyled mb-4">
              <li><strong>@lang('User:')</strong> {{ $payroll->user->first_name }} {{ $payroll->user->last_name }}</li>
              <li><strong>@lang('Email:')</strong> {{ $payroll->user->email }}</li>
              <li><strong>@lang('Employee Code:')</strong> {{ $payroll->user->code }}</li>
              <li><strong>@lang('Designation:')</strong> {{ $payroll->user->designation->name ?? 'N/A' }}</li>
              <li><strong>@lang('Phone:')</strong> {{ $payroll->user->phone }}</li>
            </ul>

            <!-- Payroll Cycle Section -->
            <h6 class="fw-bold text-primary mb-3"><i class="bx bx-calendar me-1"></i>@lang('Payroll Cycle')</h6>
            <ul class="list-unstyled mb-4">
              <li><strong>@lang('Cycle Name:')</strong> {{ $payroll->payrollCycle->name }}</li>
              <li>
                <strong>@lang('Pay Period:')</strong> {{ $payroll->payrollCycle->pay_period_start->format(Constants::DateFormat) }}
                - {{ $payroll->payrollCycle->pay_period_end->format(Constants::DateFormat) }}</li>
              <li>
                <strong>@lang('Pay Date:')</strong> {{ $payroll->payrollCycle->pay_date->format(Constants::DateFormat) }}
              </li>
              <li><strong>@lang('Status:')</strong>
                @if($payroll->status === 'paid')
                  <span class="badge bg-success">@lang('Paid')</span>
                @elseif($payroll->status === 'pending')
                  <span class="badge bg-warning">@lang('Pending')</span>
                @else
                  <span class="badge bg-secondary">{{ ucfirst($payroll->status->value) }}</span>
                @endif
              </li>
            </ul>

            <!-- Salary Details Section -->
            <h6 class="fw-bold text-primary mb-3"><i class="bx bx-wallet me-1"></i>@lang('Salary Details')</h6>
            <div class="table-responsive">
              <table class="table table-bordered mb-4">
                <thead>
                <tr>
                  <th>@lang('Basic Salary')</th>
                  <th>@lang('Gross Salary')</th>
                  <th>@lang('Net Salary')</th>
                  <th>@lang('Tax Amount')</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>${{ number_format($payroll->basic_salary, 2) }}</td>
                  <td>${{ number_format($payroll->gross_salary, 2) }}</td>
                  <td>${{ number_format($payroll->net_salary, 2) }}</td>
                  <td>${{ number_format($payroll->tax_amount, 2) }}</td>
                </tr>
                </tbody>
              </table>
            </div>

            <!-- Adjustments Section -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-primary mb-0"><i class="bx bx-adjust me-1"></i>@lang('Adjustments Log')</h6>
              {{-- Show Add button only if status allows modification --}}
              @if($canModifyAdjustments)
                <button class="btn btn-sm btn-outline-primary" id="addManualAdjustmentBtn" data-bs-toggle="modal"
                        data-bs-target="#manualAdjustmentModal">
                  <i class="bx bx-plus"></i> Add Manual Adjustment
                </button>
              @endif
            </div>
            @if($payroll->payrollAdjustmentLogs->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm table-bordered" id="adjustmentsLogTable">
                  <thead>
                  <tr>
                    <th>@lang('Name') / @lang('Notes')</th>
                    <th>@lang('Type')</th>
                    <th>@lang('Amount')</th>
                    {{-- Add actions column only if modification allowed --}}
                    @if($canModifyAdjustments)
                      <th>@lang('Actions')</th>
                    @endif
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($payroll->payrollAdjustmentLogs as $adjustment)
                    <tr data-log-id="{{ $adjustment->id }}">
                      <td>
                        {{ $adjustment->name }}
                        @if($adjustment->log_message)
                          <br><small class="text-muted fst-italic">{{ $adjustment->log_message }}</small>
                        @endif
                        @if($adjustment->is_manual)
                          <span class="badge bg-label-info ms-1">Manual</span>
                        @endif
                      </td>
                      <td>
                        @if($adjustment->type === 'benefit')
                          <span class="badge bg-label-success">@lang('Benefit')</span>
                        @else
                          <span class="badge bg-label-danger">@lang('Deduction')</span>
                        @endif
                      </td>
                      <td class="text-end">{{ $currency }}{{ number_format($adjustment->amount, 2) }}</td>
                      {{-- Add actions column only if modification allowed --}}
                      @if($canModifyAdjustments)
                        <td class="text-center">
                          @if($adjustment->is_manual)
                            {{-- Only allow deleting manual entries --}}
                            <button class="btn btn-xs btn-icon text-danger delete-manual-adjustment"
                                    data-id="{{ $adjustment->id }}"
                                    data-url="{{ route('payroll.adjustments.destroyManual', $adjustment->id) }}"
                                    {{-- Adjust route name --}}
                                    title="Delete Manual Adjustment">
                              <i class="bx bx-trash"></i>
                            </button>
                          @else
                            <span class="text-muted" title="System Generated"><i class="bx bx-cog"></i></span>
                          @endif
                        </td>
                      @endif
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <p class="text-muted" id="noAdjustmentsMessage">@lang('No adjustments applied for this period.')</p>
            @endif
          </div>
        </div>
      </div>

      {{-- Meta Info & Actions (Right Column) --}}
      <div class="col-md-4">
        <div class="card shadow-sm mb-4">
          <div class="card-header"><h6 class="mb-0"><i class="bx bx-info-circle me-1"></i>@lang('Payroll Information')
            </h6></div>
          <div class="card-body">
            <ul class="list-unstyled">
              <li><strong>@lang('Payroll Record ID:')</strong> PR-{{ $payroll->id }}</li>
              <li><strong>@lang('Cycle:')</strong> {{ $payroll->payrollCycle->name }}</li>
              <li><strong>@lang('Period:')</strong> {{ $payroll->period }}</li>
              <li><strong>@lang('Record Status:')</strong>
                @php $statusBadgeClass = $payroll->status->colorClass(); @endphp
                <span class="badge {{ $statusBadgeClass }} ms-1">{{ $payroll->status->label() }}</span>
              </li>
              <li><strong>@lang('Generated On:')</strong> {{ $payroll->created_at->format('M d, Y H:i') }}</li>
              <li><strong>@lang('Last Updated:')</strong> {{ $payroll->updated_at->diffForHumans() }}</li>
              @if($payroll->status == PayrollRecordStatus::CANCELLED && $payroll->cancel_remarks)
                <li><strong>@lang('Cancellation Reason:')</strong><br><span
                    class="text-danger small">{{ $payroll->cancel_remarks }}</span></li>
              @endif
            </ul>
          </div>
          {{-- Footer for status change actions --}}
          <div class="card-footer text-center d-grid gap-2"> {{-- Use d-grid for full width buttons --}}
            @if($canMarkCompleted)
              <button class="btn btn-sm btn-info" id="markCompletedBtn"
                      data-url="{{ route('payroll.records.markCompleted', $payroll->id) }}">
                <i class="bx bx-check-double me-1"></i> Mark as Completed
              </button>
            @endif
            @if($canMarkPaid)
              <button class="btn btn-sm btn-success" id="markPaidBtn"
                      data-url="{{ route('payroll.records.markPaid', $payroll->id) }}">
                <i class="bx bx-money-withdraw me-1"></i> Mark as Paid
              </button>
            @endif
            @if($canCancel)
              <button class="btn btn-sm btn-danger" id="cancelPayrollBtn" data-bs-toggle="modal"
                      data-bs-target="#cancelPayrollModal">
                <i class="bx bx-x-circle me-1"></i> Cancel Payroll Record
              </button>
            @endif
          </div>
        </div>
        {{-- Attendance Summary Card --}}
        @if($payroll->payslip)
          <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0"><i
                  class="bx bx-calendar-check me-1"></i>@lang('Attendance Summary')</h6></div>
            <div class="card-body">
              <dl class="row mb-0">
                <dt class="col-8">Total Scheduled Days:</dt>
                <dd class="col-4 text-end">{{ $payroll->payslip->total_working_days ?? '-' }}</dd>
                <dt class="col-8">Days Present:</dt>
                <dd
                  class="col-4 text-end">{{ $payroll->payslip->total_worked_days ?? '-' }}</dd> {{-- Check calculation --}}
                <dt class="col-8">Leave Days:</dt>
                <dd class="col-4 text-end">{{ $payroll->payslip->total_leave_days ?? '-' }}</dd>
                <dt class="col-8">Absent Days:</dt>
                <dd class="col-4 text-end">{{ $payroll->payslip->total_absent_days ?? '-' }}</dd>
                <dt class="col-8">Holidays:</dt>
                <dd class="col-4 text-end">{{ $payroll->payslip->total_holidays ?? '-' }}</dd>
                <dt class="col-8">Weekends:</dt>
                <dd class="col-4 text-end">{{ $payroll->payslip->total_weekends ?? '-' }}</dd>
                <hr class="my-2">
                <dt class="col-8">Late Count:</dt>
                <dd class="col-4 text-end">{{ $payroll->payslip->total_late_days ?? '-' }}</dd>
                <dt class="col-8">Early Leave Count:</dt>
                <dd class="col-4 text-end">{{ $payroll->payslip->total_early_checkout_days ?? '-' }}</dd>
                <dt class="col-8">Overtime Days:</dt>
                <dd class="col-4 text-end">{{ $payroll->payslip->total_overtime_days ?? '-' }}</dd>
              </dl>
            </div>
          </div>
        @endif

      </div>
    </div>

    {{-- Add Manual Adjustment Modal --}}
    <div class="modal fade" id="manualAdjustmentModal" tabindex="-1" aria-labelledby="manualAdjustmentModalLabel"
         aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="manualAdjustmentModalLabel">Add Manual Payroll Adjustment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="manualAdjustmentForm" onsubmit="return false;">
            @csrf {{-- Method is POST --}}
            <input type="hidden" name="payroll_record_id" value="{{ $payroll->id }}"> {{-- Pass record ID --}}
            {{-- <input type="hidden" name="adjustment_log_id" id="manualAdjustmentLogId" value=""> Hidden ID for Editing later --}}

            <div class="modal-body">
              {{-- Adjustment Name/Description --}}
              <div class="mb-3">
                <label class="form-label" for="manualAdjustmentName">Description / Name <span
                    class="text-danger">*</span></label>
                <input type="text" class="form-control" id="manualAdjustmentName" name="name"
                       placeholder="e.g., Performance Bonus, Lunch Deduction" required />
                <div class="invalid-feedback"></div>
              </div>
              {{-- Adjustment Type --}}
              <div class="mb-3">
                <label class="form-label" for="manualAdjustmentType">Type <span class="text-danger">*</span></label>
                <select id="manualAdjustmentType" name="type" class="form-select select2-basic" required>
                  <option value="">Select Type</option>
                  <option value="benefit">Benefit / Earning</option>
                  <option value="deduction">Deduction</option>
                </select>
                <div class="invalid-feedback"></div>
              </div>
              {{-- Adjustment Amount --}}
              <div class="mb-3">
                <label class="form-label" for="manualAdjustmentAmount">Amount ({{ $currency }}) <span
                    class="text-danger">*</span></label>
                <input type="number" class="form-control numeral-mask" id="manualAdjustmentAmount" name="amount"
                       placeholder="Enter amount" required />
                <div class="invalid-feedback"></div>
              </div>
              {{-- Notes --}}
              <div class="mb-3">
                <label class="form-label" for="manualAdjustmentNotes">Notes (Optional)</label>
                <textarea class="form-control" id="manualAdjustmentNotes" name="log_message" rows="2"
                          placeholder="Reason for manual adjustment..."></textarea>
              </div>
              <div class="mt-3 general-error-message text-danger small"></div> {{-- General error display --}}
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary" id="submitManualAdjustmentBtn">Add Adjustment</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    {{-- End Manual Adjustment Modal --}}

    {{-- Cancel Payroll Modal --}}
    <div class="modal fade" id="cancelPayrollModal" tabindex="-1" aria-labelledby="cancelPayrollModalLabel"
         aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="cancelPayrollModalLabel">Cancel Payroll Record</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form id="cancelPayrollForm" onsubmit="return false;">
            @csrf {{-- Method is POST --}}
            <div class="modal-body">
              <p>Are you sure you want to cancel this payroll record for {{ $payroll->user->getFullName() }} for the
                period {{ $payroll->period }}?</p>
              {{-- Cancellation Reason --}}
              <div class="mb-3">
                <label class="form-label" for="cancel_reason">Reason for Cancellation <span class="text-danger">*</span></label>
                <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3" required
                          placeholder="Enter reason..."></textarea>
                <div class="invalid-feedback"></div>
              </div>
              <div class="mt-3 general-error-message text-danger small"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-danger" id="submitCancelBtn">Confirm Cancellation</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    {{-- End Cancel Payroll Modal --}}
  </div>
@endsection

