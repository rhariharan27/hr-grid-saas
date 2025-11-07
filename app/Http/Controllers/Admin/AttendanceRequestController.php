<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    public function approve(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $attendanceRequest = AttendanceRequest::findOrFail($id);
        DB::transaction(function () use ($attendanceRequest, $request) {
            $attendanceRequest->status = $request->status;
            $attendanceRequest->approved_by = auth()->id();
            $attendanceRequest->approved_at = now();
            $attendanceRequest->save();

            if ($request->status === 'approved') {
                $attendance = Attendance::create([
                    'user_id' => $attendanceRequest->user_id,
                    'check_in_time' => $attendanceRequest->date . ' ' . $attendanceRequest->check_in,
                    'check_out_time' => $attendanceRequest->date . ' ' . $attendanceRequest->check_out,
                    'status' => 'present',
                    'created_by_id' => auth()->id(),
                ]);
                AttendanceLog::create([
                    'attendance_id' => $attendance->id,
                    'type' => 'check_in',
                    'created_by_id' => auth()->id(),
                ]);
                AttendanceLog::create([
                    'attendance_id' => $attendance->id,
                    'type' => 'check_out',
                    'created_by_id' => auth()->id(),
                ]);
            }
        });
        return response()->json(['message' => 'Attendance request ' . $request->status]);
    }

    public function indexAjax(Request $request)
    {
        $query = AttendanceRequest::with('user')->orderBy('date', 'desc');

        // Only apply date filter if a specific date is selected
        if ($request->filled('date') && $request->date !== '') {
            $query->whereDate('date', $request->date);
        }

        // Apply user filter if selected
        if ($request->filled('userId')) {
            $query->where('user_id', $request->userId);
        }

        return datatables()->of($query)
            ->addColumn('employee', function ($row) {
                if ($row->user) {
                    return $row->user->code . ' - ' . $row->user->getFullName();
                }
                return 'N/A';
            })
            ->addColumn('actions', function ($row) {
                if ($row->status === 'pending') {
                    $approveUrl = url('/attendance-requests/' . $row->id . '/approve');
                    $rejectUrl = url('/attendance-requests/' . $row->id . '/approve'); // Both use the same endpoint
                    return '<div class="btn-group" role="group" style="gap: 3px;">
                        <button class="btn btn-success btn-sm btn-approve" data-url="' . $approveUrl . '">Approve</button>
                        <button class="btn btn-danger btn-sm btn-reject" data-url="' . $rejectUrl . '">Reject</button>
                    </div>';
                }
                return '<span class="badge ' . ($row->status === 'approved' ? 'bg-success' : 'bg-danger') . '">' 
                    . ucfirst($row->status) . '</span>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
