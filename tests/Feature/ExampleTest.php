<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_carbon_parse()
    {
        $string = '13 мая 1961';

        $string = str($string)->replace([
            'мая'
        ], [
            'may'
        ])->toString();

        $date = Carbon::parse($string);

        dump($date);
    }
}
