<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Only eager load relationships that are actually used in export
        return Attendance::with(['user', 'shift'])
            ->get();
    }

    public function map($attendance): array
    {
        return [
            $attendance->user ? $attendance->user->code : '',
            optional($attendance->check_in_time)->format('Y-m-d H:i'),
            optional($attendance->check_out_time)->format('Y-m-d H:i'),
            $attendance->late_reason,
            $attendance->shift ? $attendance->shift->code : '',
            $attendance->early_checkout_reason,
            $attendance->working_hours,
            $attendance->late_hours,
            $attendance->early_hours,
            $attendance->overtime_hours,
            $attendance->notes,
            $attendance->status,
        ];
    }

    public function headings(): array
    {
        return [
            'employee_code',
            'check_in_time',
            'check_out_time',
            'late_reason',
            'shift_code',
            'early_checkout_reason',
            'working_hours',
            'late_hours',
            'early_hours',
            'overtime_hours',
            'notes',
            'status',
        ];
    }
}
