<?php

use Carbon\Carbon;

function isOver18(string $date_of_birth)
{

    $dob = Carbon::parse($date_of_birth);
    return (Carbon::now()->diffInYears($dob) >= 18);
}


function log_activity($userModel, string $log, $contentModel = null)
{

    $activity =  activity()
        ->causedBy($userModel);


    if ($contentModel) {
        $activity->performedOn($contentModel);
    }

    return $activity->log($log);
}


function genereateFakeAttendance()
{
    $faker = \Faker\Factory::create();

    $arrived_at = Carbon::parse($faker->dateTime(now()->addDay()));
    $arrived_at->year(today()->year);
    $timeJoin = $faker->randomElement(range(6, 12));
    $arrived_at->setHours($timeJoin);

    $left_at = $arrived_at->copy();
    $left_at->hours($timeJoin + $faker->randomElement(range(1, 9)));

    return [
        "arrived_at" => $arrived_at,
        "left_at" => $left_at,
    ];
}
