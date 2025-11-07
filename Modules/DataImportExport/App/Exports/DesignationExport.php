<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\Designation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DesignationExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return Designation::select(
      'id',
      'name',
      'code',
      'notes',
      'status',
      'level',
      'is_leave_approver',
      'is_expense_approver',
      'is_loan_approver',
      'is_document_approver',
      'is_advance_approver',
      'is_resignation_approver',
      'is_transfer_approver',
      'is_promotion_approver',
      'is_increment_approver',
      'is_training_approver',
      'is_recruitment_approver',
      'is_performance_approver',
      'is_disciplinary_approver',
      'is_complaint_approver',
      'is_warning_approver',
      'is_termination_approver',
      'is_confirmation_approver',
      'department_id',
      'parent_id',
      'created_at',
      'updated_at'
    )->get();
  }

  /**
   * Define column headings.
   *
   * @return array
   */
  public function headings(): array
  {
    return [
      'ID',
      'Name',
      'Code',
      'Notes',
      'Status',
      'Level',
      'Is Leave Approver',
      'Is Expense Approver',
      'Is Loan Approver',
      'Is Document Approver',
      'Is Advance Approver',
      'Is Resignation Approver',
      'Is Transfer Approver',
      'Is Promotion Approver',
      'Is Increment Approver',
      'Is Training Approver',
      'Is Recruitment Approver',
      'Is Performance Approver',
      'Is Disciplinary Approver',
      'Is Complaint Approver',
      'Is Warning Approver',
      'Is Termination Approver',
      'Is Confirmation Approver',
      'Department ID',
      'Parent ID',
      'Created At',
      'Updated At',
    ];
  }
}
