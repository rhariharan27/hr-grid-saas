<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\SuperAdmin\Order;
use Constants;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;


class OrderController extends Controller
{
  public function index()
  {
    return view('superAdmin.order.index');
  }

  public function getListAjax(Request $request)
  {
    try {
      // Build your base query with the necessary joins or selects
      $query = Order::query()
        ->select(
          'orders.*',
          'users.first_name as user_first_name',
          'users.last_name as user_last_name',
          'users.profile_picture as user_profile_image',
          'plan.name as plan_name',
          'plan.duration as plan_duration',
          'plan.duration_type as plan_duration_type',
          'plan.included_users as plan_included_users'
        )
        ->leftJoin('users', 'orders.user_id', '=', 'users.id')
        ->leftJoin('plans as plan', 'orders.plan_id', '=', 'plan.id');

      // If you want to filter by date, status, or anything else, handle it here
      if ($request->has('dateFilter') && !empty($request->dateFilter)) {
        $query->whereDate('orders.created_at', $request->dateFilter);
      }

      return DataTables::of($query)
        // You can add custom columns here:
        ->addColumn('user', function ($order) {
          // For example, return a small partial that shows user avatar + name
          return view('_partials._profile-avatar', [
            'user' => $order->user // or build an object with first_name/last_name
          ])->render();
        })
        // Or just combine first/last name in one column
        ->addColumn('user_name', function ($order) {
          return $order->user_first_name . ' ' . $order->user_last_name;
        })
        ->addColumn('plan_display', function ($order) {
          // e.g., Plan name + duration + included users
          $html = '<strong>' . e($order->plan_name) . '</strong><br>';
          $html .= 'Duration: ' . $order->plan_duration . ' ' . $order->plan_duration_type . '<br>';
          $html .= 'Included Users: ' . $order->plan_included_users;
          return $html;
        })
        ->addColumn('created_date', function ($order) {
          return $order->created_at
            ? $order->created_at->format('Y-m-d H:i:s')
            : '-';
        })
        ->addColumn('actions', function ($order) {
          // e.g., show + delete
          // We'll do a "show-order-details" button
          $output = '
                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-icon show-order-details" data-id="' . $order->id . '"
                                data-bs-toggle="offcanvas" data-bs-target="#offcanvasShowOrderDetails">
                                <i class="bx bx-show"></i>
                            </button>
                    ';

          if ($order->status != OrderStatus::COMPLETED && $order->status != OrderStatus::FAILED && $order->status != OrderStatus::CANCELLED && $order->status != OrderStatus::REFUNDED) {
            // Add a delete button only if the order is pending
            $output .= '<button class="btn btn-sm btn-icon delete-record" data-id="' . $order->id . '">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>';

          } else {
            $output .= '</div>';
          }

          return $output;
        })
        ->editColumn('status', function ($order) {
          if ($order->status == OrderStatus::PENDING) {
            return '<span class="badge bg-warning">Pending</span>';
          } else if ($order->status == OrderStatus::COMPLETED) {
            return '<span class="badge bg-success">Completed</span>';
          } else if ($order->status == OrderStatus::FAILED) {
            return '<span class="badge bg-danger">Failed</span>';
          } else if ($order->status == OrderStatus::CANCELLED) {
            return '<span class="badge bg-danger">Cancelled</span>';
          } else if ($order->status == OrderStatus::REFUNDED) {
            return '<span class="badge bg-danger">Refunded</span>';
          } else {
            return '<span class="badge bg-info">' . ucfirst($order->status->value) . '</span>';
          }
        })
        ->rawColumns(['actions', 'plan_display', 'user', 'status'])
        ->make(true);

    } catch (Exception $e) {
      Log::error($e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Something went wrong while fetching orders.'
      ], 500);
    }
  }

  // Show single order details
  public function getByIdAjax($id)
  {
    try {
      $order = Order::with('plan', 'user') // so we can see plan + user
      ->findOrFail($id);

      // Build your response data
      $response = [
        'id' => $order->id,
        'planName' => $order->plan ? $order->plan->name : null,
        'userName' => $order->user ? $order->user->getFullName() : null,
        'status' => $order->status,
        'type' => $order->type,
        'amount' => $order->amount,
        'totalAmount' => $order->total_amount,
        'paymentGateway' => $order->payment_gateway,
        'createdAt' => $order->created_at ? $order->created_at->format(Constants::DateTimeFormat) : null,
        'paidAt' => $order->paid_at ? $order->paid_at->format(Constants::DateTimeFormat) : null,
        'additionalUsers' => $order->additional_users,
        // etc. add more fields as needed
      ];

      return response()->json([
        'status' => 'success',
        'data' => $response
      ]);
    } catch (Exception $e) {
      Log::error('getByIdAjax error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Unable to fetch order details'
      ], 500);
    }
  }

  // Example of a "delete" route. You can decide if you want to allow order deletions
  public function deleteAjax($id)
  {
    try {
      $order = Order::findOrFail($id);
      $order->delete();
      return response()->json([
        'status' => 'success',
        'message' => 'Order deleted successfully'
      ]);
    } catch (Exception $e) {
      Log::error('Order delete error: ' . $e->getMessage());
      return response()->json([
        'status' => 'error',
        'message' => 'Unable to delete order'
      ], 500);
    }
  }
}
