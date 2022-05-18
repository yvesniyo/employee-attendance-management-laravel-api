<?php

namespace Tests\Feature;

use App\Events\EmployeeCreatedEvent;
use App\Jobs\SendResetLinkToManagerJob;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ManagerAuthTest extends TestCase
{


    use
        DatabaseTransactions;


    /**
     * @test
     */
    public function test_signup_manager()
    {


        $this->expectsEvents(EmployeeCreatedEvent::class);

        $employee = Employee::factory()->make([
            "dob" => Carbon::now()
                ->subYears(mt_rand(18, 30))
                ->format("Y-m-d")
        ])->toArray();

        $this->json(
            'POST',
            route("manager.signup"),
            $employee
        )->assertJson([
            "status" => 200,
        ]);
    }

    /**
     * @test
     */

    public function test_active_manager_login()
    {
        $manager = Employee::factory()->create([
            "status" => "ACTIVE",
            "position" => "MANAGER",
        ]);

        $credentials = $manager->only(["email"]);
        $credentials["password"] = "password";

        $this->json("POST", route("manager.login"), $credentials)
            ->assertJson([
                "status" => 200,
            ]);
    }

    public function test_suspended_manager_login()
    {
        $manager = Employee::factory()->create([
            "status" => "INACTIVE",
            "position" => "MANAGER",
        ]);

        $credentials = $manager->only(["email"]);
        $credentials["password"] = "password";

        $this->json("POST", route("manager.login"), $credentials)
            ->assertJson([
                "status" => 403,
            ]);
    }


    public function test_unknown_manager_login()
    {
        $manager = Employee::factory()->make([
            "status" => "INACTIVE",
            "position" => "MANAGER",
        ]);

        $credentials = $manager->only(["email"]);
        $credentials["password"] = "password";

        $this->json("POST", route("manager.login"), $credentials)
            ->assertJson([
                "status" => 401,
            ]);
    }




    /**
     * @test
     */
    public function test_signup_under_age_manager()
    {
        $employee = Employee::factory()->make([
            "dob" => Carbon::now()->subYears(15)->format("Y-m-d")
        ])->toArray();



        $this->json(
            'POST',
            route("manager.signup"),
            $employee
        )->assertJsonValidationErrorFor("dob");
    }

    /**
     * @test
     */
    public function test_manager_logged_in_manager()
    {
        /** @var Authenticatable*/
        $employee = Employee::factory([
            "status" => "ACTIVE"
        ])->create();

        $this->actingAs($employee)
            ->json(
                'GET',
                route("manager.me")
            )->assertJson([
                "id" => $employee->id
            ]);
    }


    public function test_manager_request_reset_link()
    {

        $this->expectsJobs(SendResetLinkToManagerJob::class);

        $manager = Employee::factory()->create([
            "position" => "MANAGER",
            "status" => "ACTIVE",
        ]);

        $this->json(
            "POST",
            route("manager.requestResetLink"),
            $manager->only("email")
        )->assertJson([
            "status" => 200
        ]);
    }

    public function test_manager_reset_password()
    {


        $this->expectsJobs([SendResetLinkToManagerJob::class]);

        $manager = Employee::factory()->create([
            "position" => "MANAGER",
            "status" => "ACTIVE",
        ]);



        $this->json(
            "POST",
            route("manager.requestResetLink"),
            $manager->only("email")
        )->assertJson([
            "status" => 200,
            "message" => "Reset link was sent to your " . $manager->email . " Account",
        ]);

        $manager->refresh();

        $data = [];
        $data["reset_code"] = $manager->reset_code;
        $data["password"] = "password";

        $this->json(
            "POST",
            route("manager.reset_password", [
                "reset_code" => $data["reset_code"]
            ]),
            $data
        )->assertJson([
            "status" => 200
        ]);
    }
}
