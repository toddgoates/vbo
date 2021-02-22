<?php

namespace Tests\Unit;

use App\Services\Reservation;
use App\Models\Ticket;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    /** @test */
    function calculating_the_total_cost()
    {
        // Arrange - create a collection of objects with a price attribute
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        // Act - create a reservation
        $reservation = new Reservation($tickets);

        // Assert - the total cost matches expectations
        $this->assertEquals(3600, $reservation->totalCost());
    }
}
