<div>
    <h4>Hi {{ $employee->name }}.</h4>
    <p>
        Your attendance was successfully recorded at
        @if ($attendance->arrived_at)
            your arrival which was on {{ $attendance->arrived_at->format('Y-m-d h:i:s A') }}
        @endif

        @if ($attendance->left_at)
            , And you signed out at {{ $attendance->left_at->format('Y-m-d h:i:s A') }}
        @endif

    </p>

</div>
