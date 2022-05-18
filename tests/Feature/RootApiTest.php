<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class RootApiTest extends TestCase
{
    use  WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_api_route_index()
    {

        $this->json(
            "GET",
            route("api-root")
        )->assertOk();
    }
}
