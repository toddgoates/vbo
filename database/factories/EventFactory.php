<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use Carbon\Carbon;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Some Event',
            'date' => Carbon::parse('+1 week'),
            'ticket_price' => 2500,
            'address' => '123 Fake Street',
            'city' => 'Salem',
            'state' => 'Utah',
            'zip' => '84653',
            'additional_information' => 'Masks required upon entry'
        ];
    }

    /**
     * Indicate that the event is published.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => Carbon::parse('-1 week')
            ];
        });
    }

    /**
     * Indicate that the event is unpublished.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unpublished()
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => null
            ];
        });
    }
}
