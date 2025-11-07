<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\Designation;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DesignationImport implements ToModel, WithHeadingRow
{
  /**
   * Create a new Designation instance for each row.
   *
   * @param array $row
   * @return Designation|null
   */
  public function model(array $row)
  {
    return new Designation([
      'name' => $row['name'],
      'code' => $row['code'],
      'notes' => $row['notes'] ?? null,
      'status' => $row['status'] ?? 'active',
      'level' => $row['level'] ?? 0,
      'is_leave_approver' => $row['is_leave_approver'] ?? false,
      'is_expense_approver' => $row['is_expense_approver'] ?? false,
      'is_loan_approver' => $row['is_loan_approver'] ?? false,
      'is_document_approver' => $row['is_document_approver'] ?? false,
      'is_advance_approver' => $row['is_advance_approver'] ?? false,
      'is_resignation_approver' => $row['is_resignation_approver'] ?? false,
      'is_transfer_approver' => $row['is_transfer_approver'] ?? false,
      'is_promotion_approver' => $row['is_promotion_approver'] ?? false,
      'is_increment_approver' => $row['is_increment_approver'] ?? false,
      'is_training_approver' => $row['is_training_approver'] ?? false,
      'is_recruitment_approver' => $row['is_recruitment_approver'] ?? false,
      'is_performance_approver' => $row['is_performance_approver'] ?? false,
      'is_disciplinary_approver' => $row['is_disciplinary_approver'] ?? false,
      'is_complaint_approver' => $row['is_complaint_approver'] ?? false,
      'is_warning_approver' => $row['is_warning_approver'] ?? false,
      'is_termination_approver' => $row['is_termination_approver'] ?? false,
      'is_confirmation_approver' => $row['is_confirmation_approver'] ?? false,
      'department_id' => $row['department_id'] ?? null,
      'parent_id' => $row['parent_id'] ?? null,
      'created_by_id' => Auth::id(),
      'updated_by_id' => Auth::id(),
    ]);
  }
}
