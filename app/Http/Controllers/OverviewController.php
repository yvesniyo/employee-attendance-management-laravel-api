<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class OverviewController extends Controller
{



    public function index()
    {
        $total_employees = Employee::count();
        $total_managers = Employee::manager()->count();

        $total_attendance_in_today = Attendance::whereDate("arrived_at", today())->count();
        $total_attendance_out_today = Attendance::whereDate("left_at", today())->count();

        return Response::json([
            "totol_employees" => $total_employees,
            "total_managers" => $total_managers,
            "total_attendance_in_today" => $total_attendance_in_today,
            "total_attendance_out_today" => $total_attendance_out_today,
        ]);
    }
}
