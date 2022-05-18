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
