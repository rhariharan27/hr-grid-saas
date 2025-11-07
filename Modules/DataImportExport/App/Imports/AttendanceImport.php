<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Shift;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class AttendanceImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    public function model(array $row)
    {
        $user = User::where('code', $row['employee_code'] ?? null)->first();
        $shift = isset($row['shift_code']) ? Shift::where('code', $row['shift_code'])->first() : null;
        // $site and $approver removed
        return new Attendance([
            'user_id' => $user ? $user->id : null,
            'check_in_time' => $row['check_in_time'] ?? null,
            'check_out_time' => $row['check_out_time'] ?? null,
            'late_reason' => $row['late_reason'] ?? null,
            'shift_id' => $shift ? $shift->id : null,
            'early_checkout_reason' => $row['early_checkout_reason'] ?? null,
            'working_hours' => $row['working_hours'] ?? null,
            'late_hours' => $row['late_hours'] ?? null,
            'early_hours' => $row['early_hours'] ?? null,
            'overtime_hours' => $row['overtime_hours'] ?? null,
            'notes' => $row['notes'] ?? null,
            'status' => $row['status'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_code' => 'required|exists:users,code',
            'check_in_time' => 'nullable|date',
            'check_out_time' => 'nullable|date',
            'shift_code' => 'nullable|exists:shifts,code',
        ];
    }
}
