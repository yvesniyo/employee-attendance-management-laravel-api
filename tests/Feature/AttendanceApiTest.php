<?php

namespace Tests\Feature;

use App\Events\EmployeeAttendanceRecordedEvent;
use App\Jobs\SendEmployeeAttendanceMailJob;
use App\Listeners\SendEmailEmployeeAttendanceNotification;
use App\Models\Employee;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class AttendanceApiTest extends TestCase
{

    use  WithoutMiddleware, DatabaseTransactions;



    /**
     * @test
     */
    public function test_assign_arrival_attendance_employee()
    {

        Bus::fake();

        Event::fake();



        $manager = Employee::manager()->active()->first();

        $employee = Employee::notManager()->active()->first();


        $this->actingAs($manager)
            ->json(
                "POST",
                route("registerAttendance"),
                [
                    "employee_id" => $employee->id,
                ]
            )->assertJson([
                "status" => 200,
                "message" => "Attendance successfully registered",
            ]);

        Event::assertDispatched(EmployeeAttendanceRecordedEvent::class);

        // Bus::assertDispatched(SendEmployeeAttendanceMailJob::class);
    }


    /**
     * @test
     */
    public function test_assign_left_shift_attendance_employee()
    {

        $this->expectsEvents([EmployeeAttendanceRecordedEvent::class]);
        // $this->expectsJobs([SendEmployeeAttendanceMailJob::class]);

        $manager = Employee::manager()->active()->first();

        $employee = Employee::notManager()->active()->first();


        $this->actingAs($manager)
            ->json(
                "POST",
                route("registerAttendance"),
                [
                    "employee_id" => $employee->id,
                ]
            )->assertJson([
                "status" => 200,
                "message" => "Attendance successfully registered",
            ]);


        $this->actingAs($manager)
            ->json(
                "POST",
                route("registerAttendance"),
                [
                    "employee_id" => $employee->id,
                ]
            )->assertJson([
                "status" => 200,
                "message" => "Attendance successfully registered",
            ]);
    }


    /**
     * @test
     */
    public function test_already_attended_employee()
    {

        $this->expectsEvents([EmployeeAttendanceRecordedEvent::class]);
        // $this->expectsJobs([SendEmployeeAttendanceMailJob::class]);

        $manager = Employee::manager()->active()->first();

        $employee = Employee::notManager()->active()->first();


        $this->actingAs($manager)
            ->json(
                "POST",
                route("registerAttendance"),
                [
                    "employee_id" => $employee->id,
                ]
            )->assertJson([
                "status" => 200,
                "message" => "Attendance successfully registered",
            ]);


        $this->actingAs($manager)
            ->json(
                "POST",
                route("registerAttendance"),
                [
                    "employee_id" => $employee->id,
                ]
            )->assertJson([
                "status" => 200,
                "message" => "Attendance successfully registered",
            ]);

        $this->actingAs($manager)
            ->json(
                "POST",
                route("registerAttendance"),
                [
                    "employee_id" => $employee->id,
                ]
            )->assertJson([
                "status" => 200,
                "message" => "Attendance is already registered for this date.",
            ]);
    }


    /**
     * @test
     */
    public function test_get_attendance()
    {

        $manager = Employee::manager()->active()->first();

        $from = now()->format("Y-m-d");
        $to = now()->format("Y-m-d");

        $this->actingAs($manager)
            ->json(
                "GET",
                route("attendances", ["from" => $from, "to" => $to]),
            )->assertJson([
                "status" => 200,
            ]);
    }


    /**
     * @test
     */
    public  function test_export_attendances()
    {
        $manager = Employee::manager()->active()->first();

        $from = now()->format("Y-m-d");
        $to = now()->format("Y-m-d");

        Storage::fake('public');

        $response =  $this->actingAs($manager)
            ->json(
                "GET",
                route("exportAttendance", ["from" => $from, "to" => $to]),
            );
    }
}
