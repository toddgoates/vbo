<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Event;
use Tests\TestCase;
use Carbon\Carbon;

class ViewEventListingTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function a_user_can_view_a_published_event()
    {
        // Arrange
        $event = Event::factory()->published()->create([
            'name' => 'School Play',
            'date' => Carbon::parse('2021-01-31 7:00pm'),
            'ticket_price' => 1000,
            'address' => '123 Fake Street',
            'city' => 'Payson',
            'state' => 'Utah',
            'zip' => '84651',
            'additional_information' => 'Refreshments available for purchase'
        ]);

        // Act
        $response = $this->get('/events/' . $event->id);

        // Assert
        $response->assertStatus(200);
        $response->assertSee('School Play');
        $response->assertSee('January 31, 2021');
        $response->assertSee('7:00pm');
        $response->assertSee('$10.00');
        $response->assertSee('123 Fake Street');
        $response->assertSee('Payson');
        $response->assertSee('Utah');
        $response->assertSee('84651');
        $response->assertSee('Refreshments available for purchase');
    }

    /** @test */
    public function a_user_cannot_view_an_unpublished_event()
    {
        // Arrange
        $event = Event::factory()->unpublished()->create();

        // Act
        $response = $this->get('/events/' . $event->id);

        // Assert
        $response->assertStatus(404);
    }
}
