<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Event;
use Tests\TestCase;
use Carbon\Carbon;

class EventTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function ticket_price_can_be_formatted_as_currency()
    {
        // Arrange - create an event with a specific ticket price
        $event = Event::factory()->make([
            'ticket_price' => 2499
        ]);

        // Assert - ticket price is formatted correctly
        $this->assertEquals('$24.99', $event->formatted_price);
    }

    /** @test */
    public function event_date_can_be_formatted()
    {
        // Arrange - create an event with a specific date
        $event = Event::factory()->make([
            'date' => Carbon::parse('2021-01-15 07:00pm')
        ]);

        // Assert - date is formatted correctly
        $this->assertEquals('January 15, 2021', $event->formatted_date);
    }

    /** @test */
    public function event_time_can_be_formatted()
    {
        // Arrange - create an event with a specific time
        $event = Event::factory()->make([
            'date' => Carbon::parse('2021-01-15 19:00')
        ]);

        // Assert - time is formatted correctly
        $this->assertEquals('7:00pm', $event->formatted_time);
    }

    /** @test */
    public function events_with_a_published_at_date_are_published()
    {
        // Arrange - create 2 published events and 1 unpublished
        $publishedEventA = Event::factory()->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedEventB = Event::factory()->create(['published_at' => Carbon::parse('-1 day')]);
        $unpublishedEventC = Event::factory()->create(['published_at' => null]);

        // Act - get all published events
        $publishedEvents = Event::published()->get();

        // Assert - The first two events are found, the last one isn't
        $this->assertTrue($publishedEvents->contains($publishedEventA));
        $this->assertTrue($publishedEvents->contains($publishedEventB));
        $this->assertFalse($publishedEvents->contains($unpublishedEventC));
    }

    /** @test */
    public function can_order_event_tickets()
    {
        // Arrange - Create an event
        $event = Event::factory()->create();

        // Act - order tickets
        $order = $event->orderTickets('todd@todd.com', 3);

        //Assert - order contains email and has correct ticket quantity
        $this->assertEquals('todd@todd.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
    }
}
