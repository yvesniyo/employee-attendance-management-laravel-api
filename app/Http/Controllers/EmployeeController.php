<?php

namespace App\Http\Controllers;

use App\Events\EmployeeCreatedEvent;
use App\Exceptions\CustomExcelImportException;
use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Services\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;


class EmployeeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }


    public function single(Request $request)
    {
        $employee = Employee::find($request->employee_id);

        if (!$employee) {
            return Response::json([
                "message" => "Employee not found",
                "status" => 404,
            ], 404);
        }

        return Response::json([
            "data" => $employee,
            "status" => 200,
        ]);
    }


    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                "name" => "string|required",
                "email" => "email|required|unique:employees,email",
                "phone" => "required|unique:employees,phone",
                "national_id" => "national_id|required|unique:employees,national_id",
                "position" => [Rule::in(["MANAGER", "DEVELOPER", "DESIGNER", "TESTER", "DEVOPS"]), "required"],
                "status" => [Rule::in(["ACTIVE", "INACTIVE"]), "required"],
                "dob" => "date|required",
            ]
        );


        $employeeDetails = $request->only([
            "dob", "national_id", "phone",
            "email", "name", "status", "position"
        ]);

        $employeeDetails["dob"] = Carbon::parse($request->dob)->format("Y-m-d");

        if (!isOver18($employeeDetails["dob"])) {
            return Response::json([
                "errors" => [
                    "dob" =>  ["Employee Date of birth should be over 18"]
                ],
                "status" => 422,
            ], 422);
        }


        $employeeDetails["code"] = CodeGenerator::EMPLOYEE();


        /** @var \App\Models\Employee */
        $employee = Employee::create($employeeDetails);


        if ($employee) {
            log_activity(auth("employee")->user(), "Created an employee", $employee);

            event(new EmployeeCreatedEvent($employee));

            return Response::json([
                "message" => $employee->name . " successfuly created",
                "status" => 200,
            ]);
        }

        return Response::json([
            "error" => "Failed to create user due to ",
            "status" => 422,
        ], 422);
    }



    public function update(Request $request, string $employee_code)
    {
        $this->validate(
            $request,
            [
                "name" => "string",
                "email" => "email",
                "phone" => "phone",
                "national_id" => "national_id",
                "position" => Rule::in(["MANAGER", "DEVELOPER", "DESIGNER", "TESTER", "DEVOPS"]),
                "status" => Rule::in(["ACTIVE", "INACTIVE"]),
                "dob" => "date",
            ]
        );

        $employeeDetails = $request->only([
            "dob", "national_id", "phone",
            "email", "name", "status", "position"
        ]);

        if (isset($employeeDetails["dob"]))
            if (!isOver18($employeeDetails["dob"])) {
                return Response::json([
                    "errors" => [
                        "dob" => ["Employee Date of birth should be over 18"]
                    ],
                    "status" => 422,
                ], 422);
            }

        /** @var \App\Models\Employee */
        $employee = Employee::whereCode($employee_code)->first();

        if (!$employee) {
            return Response::json([
                "error" => "Couldn't find the employee",
                "status" => 404,
            ], 404);
        }


        if ($employee->update($employeeDetails)) {

            log_activity(auth("employee")->user(), "Updated an employee", $employee);

            return Response::json([
                "message" => $employee->name . " successfuly updated",
                "status" => 200,
            ]);
        }

        return Response::json([
            "error" => "Failed to update user due to ",
            "status" => 500,
        ], 500);
    }





    public function delete(Request $request, string $employee_code)
    {
        /** @var \App\Models\Employee */
        $employee = Employee::whereCode($employee_code)->first();

        $employee->attendance()->delete();


        if (!$employee->delete()) {
            return Response::json([
                "error" => "Failed to delete user",
                "status" => 500,
            ], 500);
        }


        log_activity(auth("employee")->user(), "Deleted an employee", $employee);

        return Response::json([
            "message" => $employee->name . " successfuly deleted",
            "status" => 200,
        ]);
    }



    public function search(Request $request)
    {

        $employees = Employee::when($request->position, function ($q) {
            $q->wherePosition(request()->position);
        })->when($request->name, function ($q) {
            $q->where("name", "LIKE", request()->name . "%");
        })->when($request->email, function ($q) {
            $q->where("email", "LIKE", request()->email . "%");
        })->when($request->phone, function ($q) {
            $q->where("phone", request()->phone . "%");
        })->when($request->code, function ($q) {
            $q->whereCode(request()->code);
        })
            ->with("todayAttendance")
            ->orderBy("created_at", "DESC")->paginate($request->limit ?? 15);

        if ($employees) {
            return $employees;
        }

        return Response::json([
            "error" => "Failed to delete user",
            "status" => 500,
        ], 500);
    }
}
