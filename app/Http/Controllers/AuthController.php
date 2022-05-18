<?php

namespace App\Http\Controllers;

use App\Events\EmployeeCreatedEvent;
use App\Jobs\SendResetLinkToManagerJob;
use App\Models\Employee;
use App\Services\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{






    public function me(Request $request)
    {
        return $request->user();
    }

    public function login(Request $request)
    {
        $this->validate(
            $request,
            [
                "password" => "string|required",
                "email" => "email|required",
            ]
        );

        $login = $this->managerGuard()->attempt([
            "email" => $request->email,
            "password" => $request->password
        ]);

        if (!$login) {
            return Response::json([
                "message" => "Wrong Username/Password",
                "status" => 401,
            ], 401);
        }

        /** @var \App\Models\Employee */
        $manager = auth("employee")->user();
        $token = $manager->createToken('API Token')->plainTextToken;


        if (!$manager->isActive()) {
            $manager->tokens()->delete();
            return Response::json([
                "message" => "This account has been suspended!",
                "status" => 403,
            ], 403);
        }


        log_activity($manager, "Logged In");

        return Response::json([
            "message" => "Login success",
            "data" => [
                "token" => $token,
                'user' => $manager
            ],
            "status" => 200,
        ], 200);
    }





    public function signup(Request $request)
    {

        $this->validate(
            $request,
            [
                "name" => "string|required",
                "email" => "email|required|unique:employees,email",
                "phone" => "phone|unique:employees,phone",
                "national_id" => "national_id|required|unique:employees,national_id",
                "dob" => "date|required",
                "password" => "string|min:6"
            ]
        );


        $employeeDetails = $request->all();

        if (!isOver18($employeeDetails["dob"])) {
            return Response::json([
                "errors" => [
                    "dob" => ["Employee Date of birth should be over 18"]
                ],
                "status" => 422,
            ], 422);
        }

        $employeeDetails["status"] = "ACTIVE";
        $employeeDetails["position"] = "MANAGER";
        $employeeDetails["password"] = Hash::make($request->password);

        $employeeDetails["code"] = CodeGenerator::EMPLOYEE();
        $employeeDetails["dob"] = Carbon::parse($employeeDetails["dob"])->format('Y-m-d');

        /** @var \App\Models\Employee */
        $employee = Employee::create($employeeDetails);

        if ($employee) {
            log_activity($employee, "Signed Up");
            log_activity($employee, "Logged In");
            event(new EmployeeCreatedEvent($employee));

            $token = $employee->createToken('API Token')->plainTextToken;

            return Response::json([
                "data" => [
                    "token" =>  $token,
                    "user" => $employee,
                ],
                "message" => $employee->name . " successfuly created",
                "status" => 200,
            ]);
        }

        return Response::json([
            "error" => "Failed to create user due to ",
            "status" => 422,
        ], 422);
    }



    public function resetPassword(Request $request, string $reset_code)
    {
        $this->validate($request, [
            "password" => "string|min:6|required",
            "confirm_password" => "string|same:password"
        ]);




        $employee = Employee::manager()
            ->whereResetCode($reset_code)
            ->first();

        if (!$employee) {
            abort(404, "Invalid reset code or it is expired, try requesting new reset link");
        }

        $expires_in = Carbon::parse($employee->reset_code_expires_in);

        if ($expires_in->lt(Carbon::now())) {
            abort(404, "Invalid reset code or it is expired, try requesting new reset link");
        }


        $employee->password = Hash::make($request->password);
        $employee->reset_code = null;
        $employee->reset_code_expires_in = null;

        if (!$employee->save()) {
            abort(500, "Unexpected error, try again later");
        }

        return Response::json([
            "message" => "Password reset success",
            "status" => 200,
        ]);
    }


    public function sendResetLink(Request $request)
    {
        $this->validate($request, [
            "email" => "email|required",
        ]);

        /** @var \App\Models\Employee */
        $employee = Employee::manager()
            ->whereEmail($request->email)
            ->first();

        if (!$employee) {
            return Response::json([
                "error" => "There is no account registed with that email",
                "status" => 404,
            ], 404);
        }

        $uuid =  Str::uuid();

        $employee->reset_code = $uuid;
        $employee->reset_code_expires_in = Carbon::now()->addHours(2);

        if (!$employee->save()) {

            return Response::json([
                "error" => "Failed to reset password",
                "status" => 500,
            ], 500);
        }

        dispatch(new SendResetLinkToManagerJob($employee));


        log_activity($employee, "Requested Reset Password Link");

        return Response::json([
            "message" => "Reset link was sent to your " . $employee->email . " Account",
            "status" => 200,
        ], 200);
    }



    public function logout(Request $request)
    {

        $user = $request->user();



        $user->tokens()->delete();

        log_activity($user, "Logout");

        return Response::json([
            "message" => "Logout success",
            "status" => 200,
        ], 200);
    }



    public function viewResetPage(Request $request, string $reset_code)
    {
        $employee = Employee::manager()
            ->whereResetCode($reset_code)
            ->first();

        if (!$employee) {
            abort(404, "Invalid reset code or it is expired, try requesting new reset link");
        }

        $expires_in = Carbon::parse($employee->reset_code_expires_in);

        if ($expires_in->lt(Carbon::now())) {
            abort(404, "Invalid reset code or it is expired, try requesting new reset link");
        }

        return view("pages.reset_page")->with("reset_code", $reset_code);
    }

    /**
     * Get auth guard for manager
     *
     * @return Illuminate\Support\Facades\Auth
     */
    public function managerGuard()
    {
        return Auth::guard("employee");
    }
}
