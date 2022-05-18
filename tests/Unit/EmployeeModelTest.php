<?php

namespace Unit;

use App\Models\Employee;
use Tests\TestCase;

class EmployeeModelTest extends TestCase
{

    /**
     * @test
     */
    public function test_employee_create()
    {
        $employee = Employee::factory()->create();

        $this->assertModelExists($employee);
    }


    /**
     * @test
     */
    public function test_employee_delete()
    {
        $employee = Employee::factory()->create();
        $employee->delete();

        $this->assertModelMissing($employee);
    }

    /**
     * @test
     */
    public function test_employee_update()
    {
        $employee = Employee::factory()->create();

        $newData = Employee::factory()->makeOne()->toArray();

        $updated = $employee->update(
            $newData
        );

        $this->assertTrue($updated);

        $this->assertEquals($employee->name, $newData['name']);
        $this->assertEquals($employee->status, $newData['status']);
        $this->assertEquals($employee->email, $newData['email']);
        $this->assertEquals($employee->position, $newData['position']);
        $this->assertEquals($employee->national_id, $newData['national_id']);
        $this->assertEquals($employee->code, $newData['code']);
    }

    /**
     * @test
     */
    public function test_employee_find_by_code()
    {
        $employee = Employee::factory()->create();

        $employee_find = Employee::whereCode($employee->code)->first();

        $this->assertNotNull($employee_find);
    }


    /**
     * @test
     */
    public function test_employee_find_by_id()
    {
        $employee = Employee::factory()->create();

        $employee_find = Employee::find($employee->id);

        $this->assertNotNull($employee_find);
    }
}
