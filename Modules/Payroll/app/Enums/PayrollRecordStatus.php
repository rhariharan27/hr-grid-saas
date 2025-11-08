<?php

namespace Modules\Payroll\Enums;

enum PayrollRecordStatus: string
{
  case PENDING = 'pending';         // Initial state after generation
  case PROCESSED = 'processed';     // Optional: After initial checks/before finalization
  case COMPLETED = 'completed';     // Finalized, ready for payment
  case PAID = 'paid';             // Payment disbursed
  case CANCELLED = 'cancelled';     // Payroll run cancelled for this record

  public function label(): string
  {
    return match ($this) {
      self::PENDING => 'Pending',
      self::PROCESSED => 'Processed',
      self::COMPLETED => 'Completed',
      self::PAID => 'Paid',
      self::CANCELLED => 'Cancelled',
    };
  }

  public function colorClass(): string
  {
    return match ($this) {
      self::PENDING => 'bg-warning',
      self::PROCESSED => 'bg-info',
      self::COMPLETED => 'bg-success',
      self::PAID => 'bg-primary',
      self::CANCELLED => 'bg-danger',
    };
  }
}
