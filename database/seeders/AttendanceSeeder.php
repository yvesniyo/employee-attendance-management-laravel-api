<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        $employees = Employee::select("id")->get()->pluck("id");

        $dates = collect([]);

        for ($i = 0; $i < 1000; $i++) {
            $dates->add(genereateFakeAttendance());
        }


        Attendance::factory()
            ->count(600)
            ->sequence(function ($data) use ($employees, $dates) {
                $date = $dates->random();

                return [
                    "employee_id" => $employees->random(),
                    "arrived_at" => $date["arrived_at"],
                    "left_at" => $date["left_at"],
                ];
            })
            ->create();
    }
}
