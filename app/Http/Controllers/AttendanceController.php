<?php

namespace App\Http\Controllers;

use App\Events\EmployeeAttendanceRecordedEvent;
use App\Models\Attendance;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{

    public function registerAttendance(StoreAttendanceRequest $request)
    {


        $employee = Employee::find($request->employee_id);

        $date = now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('arrived_at', $date)
            ->first();


        $wasFirstAttendance = false;

        if (!$attendance) {

            $wasFirstAttendance = true;


            $attendance = Attendance::create([
                "employee_id" => $employee->id,
                "arrived_at" => $date,
            ]);
        }

        if ($attendance->left_at != null && $attendance->arrived_at != null) {
            return Response::json([
                "status" => 200,
                "data" => $attendance,
                "message" => "Attendance is already registered for this date.",
            ]);
        }

        if (!$wasFirstAttendance) {
            $attendance->left_at = $date;
            $attendance->save();
        }


        event(new EmployeeAttendanceRecordedEvent($employee, $attendance));

        return Response::json([
            "status" => 200,
            "data" => $attendance,
            "message" => "Attendance successfully registered",
        ]);
    }


    public function getAttendance(Request $request)
    {


        $from = Carbon::parse($request->from)->addDay();
        $to = Carbon::parse($request->to)->addDay();

        $attendances = Attendance::whereDate("arrived_at", ">=", $from)
            ->whereDate("arrived_at", "<=", $to)
            ->where([
                ["arrived_at", "<>", null],
                // ["left_at", "<>", null],
            ])
            ->with("employee:id,name,code")
            ->orderBy("arrived_at", "desc")
            ->get()
            ->groupBy(function ($data) {
                return Carbon::parse($data->arrived_at)->format("Y-m-d");
            })->map(function ($data, $key) {
                $data = $data->map(function ($data) {

                    $data["time_arrived_at"] = null;
                    $data["time_left_at"] = null;

                    if ($data->arrived_at)
                        $data["time_arrived_at"] =  Carbon::parse($data->arrived_at)->format("h:i:s A");

                    if ($data->left_at)
                        $data["time_left_at"] = Carbon::parse($data->left_at)->format("h:i:s A");
                    return  $data;
                });

                return [
                    "date" => $key,
                    "data" => $data,
                ];
            });

        $newMappedAttendances = [];
        foreach ($attendances as $key => $value) {
            $newMappedAttendances[] = $value;
        }

        return Response::json([
            "status" => 200,
            "data" => $newMappedAttendances,
        ]);
    }


    public function exportAttendance(Request $request)
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columns = [
            "DATE",
            "CODE",
            "NAME",
            "Arrived At",
            "Left At",
        ];

        $letters = [
            "A", "B", "C", "D", "E", "F", "G", "H", "I",
            "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z"
        ];

        foreach ($columns as $key => $column) {
            $sheet->setCellValue($letters[$key] . "1", $column);
        }

        $from = Carbon::parse($request->from)->addDay()->format("Y-m-d");
        $to = Carbon::parse($request->to)->addDay()->format("Y-m-d");

        $attendances = Attendance::whereDate("arrived_at", ">=", $from)
            ->whereDate("arrived_at", "<=", $to)
            ->where([
                ["arrived_at", "<>", null],
            ])
            ->with("employee:id,name,code")
            ->orderBy("arrived_at", "desc")
            ->get()
            ->map(function ($data) {
                return [
                    Carbon::parse($data->arrived_at)->format("Y-m-d"),
                    $data->employee->code,
                    $data->employee->name,
                    $data->arrived_at,
                    $data->left_at,
                ];
            });

        $row = 2;
        foreach ($attendances as $key => $attendance) {

            foreach ($columns as $keyColumn => $column) {
                $sheet->setCellValue($letters[$keyColumn] . $row, $attendance[$keyColumn]);
            }
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        $random_uuid =  Str::uuid();
        $fileName = "export_{$from}_{$to}_{$random_uuid}.xlsx";

        $path = "storage/reports";

        if (!File::exists("{$path}")) {
            File::makeDirectory("{$path}", 0777, true, true);
        }

        $writer->save("{$path}/{$fileName}");

        return Response::json([
            "status" => 200,
            "data" => [
                "link" => url("{$path}/{$fileName}")
            ]
        ]);
    }
}
