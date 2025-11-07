<?php

namespace Modules\DataImportExport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\DataImportExport\App\Exports\ClientExport;
use Modules\DataImportExport\App\Exports\DepartmentExport;
use Modules\DataImportExport\App\Exports\DesignationExport;
use Modules\DataImportExport\App\Exports\EmployeeExport;
use Modules\DataImportExport\App\Exports\ExpenseTypeExport;
use Modules\DataImportExport\App\Exports\HolidayExport;
use Modules\DataImportExport\App\Exports\LeaveTypeExport;
use Modules\DataImportExport\App\Exports\ProductCategoryExport;
use Modules\DataImportExport\App\Exports\ProductExport;
use Modules\DataImportExport\App\Exports\ShiftExport;
use Modules\DataImportExport\App\Exports\TeamExport;
use Modules\DataImportExport\App\Imports\ClientImport;
use Modules\DataImportExport\App\Imports\DepartmentImport;
use Modules\DataImportExport\App\Imports\DesignationImport;
use Modules\DataImportExport\App\Imports\EmployeeImport;
use Modules\DataImportExport\App\Imports\ExpenseTypeImport;
use Modules\DataImportExport\App\Imports\HolidayImport;
use Modules\DataImportExport\App\Imports\LeaveTypeImport;
use Modules\DataImportExport\App\Imports\ProductCategoryImport;
use Modules\DataImportExport\App\Imports\ProductImport;
use Modules\DataImportExport\App\Imports\ShiftImport;
use Modules\DataImportExport\App\Imports\TeamImport;

class DataImportExportController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    return view('dataimportexport::index');
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    return view('dataimportexport::create');
  }

  public function import(Request $request)
  {
    $request->validate([
      'file' => 'required|file',
      'type' => 'required'
    ]);

    $file = $request->file('file');

    try {

      switch ($request->type) {
        case 'attendances':
          Excel::import(new \Modules\DataImportExport\App\Imports\AttendanceImport(), $file);
          break;
        case 'holidays':
          Excel::import(new HolidayImport(), $file);
          break;
        case 'leave-types':
          Excel::import(new LeaveTypeImport(), $file);
          break;
        case 'expense-types':
          Excel::import(new ExpenseTypeImport(), $file);
          break;
        case 'teams':
          Excel::import(new TeamImport(), $file);
          break;
        case 'shifts':
          Excel::import(new ShiftImport(), $file);
          break;
        case 'departments':
          Excel::import(new DepartmentImport(), $file);
          break;
        case 'designations':
          Excel::import(new DesignationImport(), $file);
          break;
        case 'clients':
          Excel::import(new ClientImport(), $file);
          break;
        case 'categories':
          Excel::import(new ProductCategoryImport(), $file);
          break;
        case 'products':
          Excel::import(new ProductImport(), $file);
          break;
        case 'employees':
          Excel::import(new EmployeeImport(), $file);
          break;
        default:
          return redirect()->route('dataImportExport.index')->with('error', 'Invalid import type');
      }

      return redirect()->route('dataImportExport.index')->with('success', 'Data imported successfully');
    } catch (Exception $e) {
      Log::error($e->getMessage());
      return redirect()->route('dataImportExport.index')->with('error', 'Error importing data');
    }

  }

  public function export($type)
  {
    try {

      return match ($type) {
        'attendances' => Excel::download(new \Modules\DataImportExport\App\Exports\AttendanceExport(), time() . '_attendances.xlsx'),
        'holidays' => Excel::download(new HolidayExport(), time() . '_holidays.xlsx'),
        'leave-types' => Excel::download(new LeaveTypeExport(), time() . '_leave_types.xlsx'),
        'expense-types' => Excel::download(new ExpenseTypeExport(), time() . '_expense_types.xlsx'),
        'teams' => Excel::download(new TeamExport(), time() . '_teams.xlsx'),
        'shifts' => Excel::download(new ShiftExport(), time() . '_shifts.xlsx'),
        'departments' => Excel::download(new DepartmentExport(), time() . '_departments.xlsx'),
        'designations' => Excel::download(new DesignationExport(), time() . '_designations.xlsx'),
        'clients' => Excel::download(new ClientExport(), time() . '_clients.xlsx'),
        'categories' => Excel::download(new ProductCategoryExport(), time() . '_product_categories.xlsx'),
        'products' => Excel::download(new ProductExport(), time() . '_products.xlsx'),
        'employees' => Excel::download(new EmployeeExport(), time() . '_employees.xlsx'),
        default => redirect()->route('dataImportExport.index')->with('error', 'Invalid export type'),
      };
    } catch (Exception $e) {
      return redirect()->route('dataImportExport.index')->with('error', 'Error exporting data');
    }
  }

}
