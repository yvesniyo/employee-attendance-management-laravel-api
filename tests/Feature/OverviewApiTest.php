<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class OverviewApiTest extends TestCase
{

    use  WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_assign_arrival_attendance_employee()
    {



        $manager = Employee::manager()->active()->first();

        $total_employees = Employee::count();
        $total_managers = Employee::manager()->count();

        $total_attendance_in_today = Attendance::whereDate("arrived_at", today())->count();
        $total_attendance_out_today = Attendance::whereDate("left_at", today())->count();


        $this->actingAs($manager)
            ->json(
                "GET",
                route("overview")
            )->assertJson([
                "totol_employees" => $total_employees,
                "total_managers" => $total_managers,
                "total_attendance_in_today" => $total_attendance_in_today,
                "total_attendance_out_today" => $total_attendance_out_today,
            ]);
    }
}
