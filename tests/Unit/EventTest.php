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
        $event = Event::factory()->make([
            'ticket_price' => 2499
        ]);

        $this->assertEquals('$24.99', $event->formatted_price);
    }

    /** @test */
    public function event_date_can_be_formatted()
    {
        $event = Event::factory()->make([
            'date' => Carbon::parse('2021-01-15 07:00pm')
        ]);

        $this->assertEquals('January 15, 2021', $event->formatted_date);
    }

    /** @test */
    public function event_time_can_be_formatted()
    {
        $event = Event::factory()->make([
            'date' => Carbon::parse('2021-01-15 19:00')
        ]);

        $this->assertEquals('7:00pm', $event->formatted_time);
    }

    /** @test */
    public function events_with_a_published_at_date_are_published()
    {
        $publishedEventA = Event::factory()->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedEventB = Event::factory()->create(['published_at' => Carbon::parse('-1 day')]);
        $unpublishedEventC = Event::factory()->create(['published_at' => null]);

        $publishedEvents = Event::published()->get();

        $this->assertTrue($publishedEvents->contains($publishedEventA));
        $this->assertTrue($publishedEvents->contains($publishedEventB));
        $this->assertFalse($publishedEvents->contains($unpublishedEventC));
    }
}
