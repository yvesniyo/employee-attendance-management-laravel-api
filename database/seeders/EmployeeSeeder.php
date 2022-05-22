<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Services\CodeGenerator;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numberToCreate = 100;

        $employeeCodes = collect([]);

        $alreadyInDbEmployeeCodes = Employee::select("code")->get()->pluck("code");

        $i = 0;
        while ($i  <= $numberToCreate) {
            $code = CodeGenerator::EMPLOYEE(false);
            if (
                !$employeeCodes->contains($code) &&
                !$alreadyInDbEmployeeCodes->contains($code)
            ) {
                $employeeCodes->add($code);
                $i++;
            }
        }

        Employee::factory($numberToCreate)
            ->sequence(function ($data) use ($employeeCodes) {
                return [
                    "code" => $employeeCodes->pop()
                ];
            })->create();
    }
}
