<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Exceptions\NotEnoughTicketsException;
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
        $event = Event::factory()->create()->addTickets(3);

        // Act - order tickets
        $order = $event->orderTickets('todd@todd.com', 3);

        //Assert - order contains email and has correct ticket quantity
        $this->assertEquals('todd@todd.com', $order->email);
        $this->assertEquals(3, $order->ticketsQuantity());
    }

    /** @test */
    function can_add_tickets()
    {
        // Arrange - create an event
        $event = Event::factory()->create();

        // Act - add tickets to it
        $event->addTickets(50);

        // Assert - the event has the correct amount of tickets
        $this->assertEquals(50, $event->ticketsRemaining());
    }

    /** @test */
    function tickets_remaining_excludes_tickets_associated_with_order()
    {
        // Arrange - create an event with tickets
        $event = Event::factory()->create()->addTickets(50);

        // Act - order tickets
        $event->orderTickets('todd@todd.com', 30);

        // Assert - the correct amount of tickets are available
        $this->assertEquals(20, $event->ticketsRemaining());
    }

    /** @test */
    function trying_to_purchase_too_many_tickets_throws_an_exception()
    {
        // Arrange - create an event with tickets
        $event = Event::factory()->create()->addTickets(50);

        // Act - order tickets
        try {
            $event->orderTickets('todd@todd.com', 51);
        } catch (NotEnoughTicketsException $e) {
            // Assert - an order is not created and the same number of tickets remain
            $this->assertFalse($event->hasOrderFor('todd@todd.com'));
            $this->assertEquals(50, $event->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets');
    }

    /** @test */
    function cannot_order_tickets_that_have_already_been_purchased()
    {
        // Arrange - create an event
        $event = Event::factory()->create()->addTickets(10);

        // Act - Purchase tickets, then have next person try to order more than what remain
        $event->orderTickets('todd@todd.com', 8);

        try {
            $event->orderTickets('toddjr@todd.com', 3);
        } catch (NotEnoughTicketsException $e) {
            // Assert - no order created for jr and the correct number of tickets remain
            $this->assertFalse($event->hasOrderFor('toddjr@todd.com'));
            $this->assertEquals(2, $event->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets');
    }
}
