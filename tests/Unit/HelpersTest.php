<?php

namespace Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;


class HelpersTest extends TestCase
{


    use
        WithoutMiddleware,
        DatabaseTransactions;

    /**
     * @test
     */
    public function test_is_over_18_years_function()
    {

        $over18_years = now()->subYears(19)->format("Y-m-d");

        $this->assertTrue(isOver18($over18_years));
        $this->assertFalse(!isOver18($over18_years));
        $this->assertIsBool(isOver18($over18_years));


        $under18_years = now()->subYears(17)->format("Y-m-d");

        $this->assertFalse(isOver18($under18_years));
        $this->assertTrue(!isOver18($under18_years));
        $this->assertIsBool(isOver18($under18_years));
    }

    /**
     * @test
     */
    public function test_can_generate_random_attendance_time()
    {

        $times = genereateFakeAttendance();

        $this->assertIsArray($times);
        $this->assertArrayHasKey("arrived_at", $times);
        $this->assertArrayHasKey("left_at", $times);
        $this->assertNotNull($times["arrived_at"]);
        $this->assertNotNull($times["left_at"]);
    }
}
