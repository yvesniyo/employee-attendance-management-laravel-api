<?php

namespace Tests\Feature;

use App\Events\EmployeeAttendanceRecordedEvent;
use App\Jobs\SendEmployeeAttendanceMailJob;
use App\Listeners\SendEmailEmployeeAttendanceNotification;
use App\Mail\EmployeeAttendanceMail;
use App\Models\Attendance;
use App\Models\Employee;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class AttendanceNotificationTest extends TestCase
{

    use  WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_attendance_listeners_attached_to_event()
    {
        Event::fake();
        Event::assertListening(
            EmployeeAttendanceRecordedEvent::class,
            SendEmailEmployeeAttendanceNotification::class
        );
    }

    /**
     * @test
     */
    public function test_attendance_is_sent_to_user()
    {

        Mail::fake();

        $employee = Employee::factory()->create();
        $attendance = Attendance::factory()->create([
            "employee_id" => $employee->id,
            "arrived_at" => now(),
            "left_at" => now(),
        ]);

        $event = new EmployeeAttendanceRecordedEvent($employee, $attendance);

        $notification = new SendEmailEmployeeAttendanceNotification();
        $notification->handle($event);


        Mail::assertSent(EmployeeAttendanceMail::class);
    }
}
