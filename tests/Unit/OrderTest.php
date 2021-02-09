<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

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
