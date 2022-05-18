<?php

namespace Unit;

use App\Models\Attendance;
use App\Models\Employee;
use Tests\TestCase;

class AttendanceModelTest extends TestCase
{

    /**
     * @test
     */
    public function test_attendance_create()
    {
        $attendance = Attendance::factory()->makeOne();
        $employee = Employee::factory()->create();
        $date = genereateFakeAttendance();


        $attendance = Attendance::create([
            "employee_id" => $employee->id,
            "arrived_at" => $date["arrived_at"],
            "left_at" => $date["left_at"],
        ]);

        $this->assertModelExists($attendance);
    }


    /**
     * @test
     */
    public function test_attendance_delete()
    {
        $employee = Employee::factory()->create();
        $date = genereateFakeAttendance();


        $attendance = Attendance::create([
            "employee_id" => $employee->id,
            "arrived_at" => $date["arrived_at"],
            "left_at" => $date["left_at"],
        ]);
        $attendance->delete();

        $this->assertModelMissing($attendance);
    }

    /**
     * @test
     */
    public function test_attendance_update()
    {
        $employee = Employee::factory()->create();

        $date = genereateFakeAttendance();


        $attendance = Attendance::create([
            "employee_id" => $employee->id,
            "arrived_at" => $date["arrived_at"],
            "left_at" => $date["left_at"],
        ]);

        $newData = Attendance::factory()->makeOne()->toArray();

        $updated = $attendance->update(
            $newData
        );

        $this->assertTrue($updated);
    }




    /**
     * @test
     */
    public function test_attendance_find_by_id()
    {
        $employee = Employee::factory()->create();
        $date = genereateFakeAttendance();


        $attendance = Attendance::create([
            "employee_id" => $employee->id,
            "arrived_at" => $date["arrived_at"],
            "left_at" => $date["left_at"],
        ]);

        $attendance_find = Attendance::find($attendance->id);

        $this->assertNotNull($attendance_find);
    }
}
