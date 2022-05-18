<?php

namespace App\Mail;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmployeeAttendanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Employee  $employee;
    public Attendance $attendance;
    public $company_name;

    public function __construct(Employee $employee, Attendance $attendance)
    {
        $this->employee = $employee;
        $this->attendance = $attendance;
        $this->company_name = config("app.name");
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.employees.attendance-record')
            ->with("employee", $this->employee)
            ->with("attendance", $this->attendance)
            ->with("company_name", $this->company_name);
    }
}
