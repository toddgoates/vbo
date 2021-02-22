<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function creating_an_order_from_tickets_email_and_amount()
    {
        // Arrange - create an event with tickets
        $event = Event::factory()->create()->addTickets(5);
        $this->assertEquals(5, $event->ticketsRemaining());

        // Act - place an order for 3 tickets
        $order = Order::forTickets($event->findTickets(3), 'todd@todd.com', 3600);

        // Assert
        // Order exists for the user
        $this->assertEquals('todd@todd.com', $order->email);

        // The correct tickets were ordered
        $this->assertEquals(3, $order->ticketQuantity());

        // The correct amount was charged
        $this->assertEquals(3600, $order->amount);

        // The correct number of tickets remain
        $this->assertEquals(2, $event->ticketsRemaining());
    }

    /** @test */
    function converting_to_an_array()
    {
        // Arrange - create an event with tickets and order all of them
        $event = Event::factory()->create(['ticket_price' => 1200])->addTickets(5);
        $order = $event->orderTickets('todd@todd.com', 5);

        // Act - convert order to an array
        $result = $order->toArray();

        // Assert - array looks like what we're expecting
        $this->assertEquals([
            'email' => 'todd@todd.com',
            'ticket_quantity' => 5,
            'amount' => 6000,
        ], $result);
    }

    /** @test */
    function tickets_are_released_when_an_order_is_cancelled()
    {
        // Arrange - create an event with tickets
        $event = Event::factory()->create()->addTickets(10);

        // Act - order tickets, then cancel the order
        $order = $event->orderTickets('todd@todd.com', 5);
        $this->assertEquals(5, $event->ticketsRemaining());

        $order->cancel();

        // Assert - the original number of tickets is the same, no order
        $this->assertEquals(10, $event->ticketsRemaining());
        $this->assertNull(Order::find($order->id));
    }
}
