<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;

class AttendanceRequestController extends Controller
{
    public function getAll(Request $request)
    {
        $skip = $request->skip;
        $take = $request->take ?? 10;

        $attendanceRequests = AttendanceRequest::query()
            ->where('user_id', auth()->id())
            ->orderBy('id', 'desc');

        if ($request->has('status')) {
            $attendanceRequests->where('status', $request->status);
        }

        if ($request->has('date') && !empty($request->date)) {
            $attendanceRequests->whereDate('date', $request->date);
        }

        $totalCount = $attendanceRequests->count();
        $attendanceRequests = $attendanceRequests->skip($skip)->take($take)->get();

        $attendanceRequests = $attendanceRequests->map(function ($attendanceRequest) {
            return [
                'id' => $attendanceRequest->id,
                'date' => $attendanceRequest->date,
                'check_in' => $attendanceRequest->check_in,
                'check_out' => $attendanceRequest->check_out,
                'reason' => $attendanceRequest->reason,
                'status' => $attendanceRequest->status,
                'createdAt' => $attendanceRequest->created_at,
                'approvedAt' => $attendanceRequest->approved_at ?? '',
                'approvedBy' => $attendanceRequest->approved_by_id ? 'Admin' : '',
            ];
        });

        $response = [
            'totalCount' => $totalCount,
            'values' => $attendanceRequests
        ];

        return response()->json($response);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'required|date_format:H:i|after:check_in',
            'reason' => 'nullable|string',
        ]);

        $attendanceRequest = AttendanceRequest::create([
            'user_id' => auth()->id(),
            'date' => $validated['date'],
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json($attendanceRequest, 201);
    }
}
