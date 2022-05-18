<?php

namespace Tests\Feature;

use ApiTestTrait;
use App\Events\EmployeeCreatedEvent;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ManagerApiTest extends TestCase
{
    use  WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_employee()
    {
        $this->expectsEvents(EmployeeCreatedEvent::class);

        $employee = Employee::factory()->make([
            "dob" => Carbon::now()
                ->subYears(mt_rand(18, 30))
                ->format("Y-m-d")
        ])->toArray();


        $manager = Employee::manager()->active()->first();

        $this->actingAs($manager)
            ->json(
                'POST',
                route("create-employee"),
                $employee
            )->assertJson([
                "status" => 200,
            ]);
    }


    /**
     * @test
     */
    public function test_update_employee()
    {
        $employee_update_data = Employee::factory()->make()->toArray();

        $employee_update_data["dob"] = Carbon::now()
            ->subYears(mt_rand(18, 30))
            ->format("Y-m-d");
        unset($employee_update_data["code"]);

        $employee_to_update = Employee::all()->random();

        $manager = Employee::manager()
            ->active()
            ->first();
        $this->actingAs($manager)
            ->json(
                'PATCH',
                route("update-employee", [
                    "employee_code" => $employee_to_update->code
                ]),
                $employee_update_data
            )->assertJson([
                "status" => 200,
            ]);


        $updatedEmployee = Employee::find($employee_to_update->id);

        $this->assertEquals($employee_update_data["status"], $updatedEmployee->status);
        $this->assertEquals($employee_update_data["name"], $updatedEmployee->name);
        $this->assertEquals($employee_update_data["email"], $updatedEmployee->email);
        $this->assertEquals($employee_update_data["dob"], $updatedEmployee->dob);
        $this->assertEquals($employee_update_data["national_id"], $updatedEmployee->national_id);
        $this->assertEquals($employee_update_data["phone"], $updatedEmployee->phone);
        $this->assertEquals($employee_update_data["position"], $updatedEmployee->position);
    }


    /**
     * @test
     */
    public function test_get_single_employee()
    {

        $manager = Employee::manager()
            ->active()
            ->first();

        $employee = Employee::notManager()
            ->active()
            ->first();


        $this->actingAs($manager)
            ->json(
                'GET',
                route("get-employee", [
                    "employee_id" => $employee->id
                ])
            )->assertJson([
                "data" => [
                    "id" => $employee->id,
                    "name" => $employee->name,
                ],
                "status" => 200,
            ])->json();
    }


    /**
     * @test
     */
    public function test_delete_employee()
    {
        $manager = Employee::manager()
            ->active()
            ->first();

        $employee = Employee::notManager()
            ->active()
            ->first();


        $this->actingAs($manager)
            ->json(
                'DELETE',
                route("delete-employee", [
                    "employee_code" => $employee->code
                ])
            )->assertJson([
                "status" => 200,
            ])->json();

        $deletedEmployee = Employee::find($employee->id);

        $this->assertNull($deletedEmployee);
    }
}
