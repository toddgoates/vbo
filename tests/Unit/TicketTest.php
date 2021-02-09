<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Event;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function a_ticket_can_be_released()
    {
        // Arrange
        // Create an event with one ticket
        $event = Event::factory()->create();
        $event->addTickets(1);

        // Create an order and assert that the ticket is tied to it
        $order = $event->orderTickets('todd@todd.com', 1);
        $ticket = $order->tickets()->first();
        $this->assertEquals($order->id, $ticket->order_id);

        // Act - release the ticket
        $ticket->release();

        // Assert the ticket's order ID is null again
        $this->assertNull($ticket->fresh()->order_id);
    }
}
