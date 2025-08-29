<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_api_messages_index_route(): void
    {
        $response = $this->getJson(route('api.messages.index'));

        $response->assertOk();
    }
}
