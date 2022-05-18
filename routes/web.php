<?php

use App\Events\EmployeeAttendanceRecordedEvent;
use App\Mail\EmployeeAttendanceMail;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


Route::get('/', function (Request $request) {

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

    $from = Carbon::parse();
    $to = Carbon::parse();

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

    $fileName = "export.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
    $writer->save('php://output');
});
