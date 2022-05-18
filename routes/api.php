<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OverviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

if (!defined("API_NAME")) {
    define("API_NAME", config("app.name") . " - API");
}





Route::get('/', function (Request $request) {
    return API_NAME;
});


Route::group(["prefix" => "/v1"], function () {

    Route::get('/', function () {
        return API_NAME;
    })->name("api-root");


    Route::group(["prefix" => "auth"], function () {

        Route::middleware('auth:sanctum')
            ->get('user', [AuthController::class, "me"])->name("manager.me");

        Route::post("login", [AuthController::class, "login"])->name("manager.login");
        Route::post("signup",  [AuthController::class, "signup"])->name("manager.signup");
        Route::post("logout", [AuthController::class, "logout"])->middleware("auth:sanctum");

        Route::post("requestResetLink",  [AuthController::class, "sendResetLink"])->name("manager.requestResetLink");
        Route::post("resetPassword/{reset_code}", [AuthController::class, "resetPassword"])->name("manager.reset_password");
    });


    Route::get("/reset-link/{reset_code}", [AuthController::class, "viewResetPage"])->name("manager.reset_link");

    Route::get("overview", [OverviewController::class, "index"])->name("overview");


    Route::get("get-attendances", [AttendanceController::class, "getAttendance"])->name("attendances");
    Route::get("exportAttendance", [AttendanceController::class, "exportAttendance"])->name("exportAttendance");


    Route::group(["prefix" => "employees", "middleware" => "auth:sanctum"], function () {

        Route::get("/search", [EmployeeController::class, "search"]);
        Route::get("/get-employee/{employee_id}", [EmployeeController::class, "single"])->name("get-employee");
        Route::post("", [EmployeeController::class, "store"])->name("create-employee");
        Route::patch("{employee_code}/update", [EmployeeController::class, "update"])->name("update-employee");
        Route::delete("{employee_code}/delete", [EmployeeController::class, "delete"])->name("delete-employee");
        Route::post("registerAttendance", [AttendanceController::class, "registerAttendance"])->name("registerAttendance");
    });
});
