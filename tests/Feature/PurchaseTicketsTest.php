<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\Billing\FakePaymentGateway;
use App\Services\Billing\PaymentGateway;
use App\Models\Event;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($event, $params)
    {
        $response = $this->json('POST', "/events/{$event->id}/orders", $params);

        return $response;
    }

    private function assertValidationError($response, $field)
    {
        $response->assertStatus(422);
        $this->assertArrayHasKey($field, $response->decodeResponseJson()['errors']);
    }

    /** @test */
    function a_customer_can_purchase_tickets_to_a_published_event()
    {
        // Arrange - create an event
        $event = Event::factory()->published()->create([
            'ticket_price' => 2499
        ]);

        // Act - purchase tickets
        $response = $this->orderTickets($event, [
            'email' => 'todd@test.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        // 201 HTTP response status
        $response->assertStatus(201);

        // Make sure the customer was charged the correct amount
        $this->assertEquals(7497, $this->paymentGateway->totalCharges());

        // Make sure an order exists for that customer
        $order = $event->orders()->where('email', 'todd@test.com')->first();
        $this->assertNotNull($order);

        // Make sure the order has the right amount of tickets
        $this->assertEquals(3, $order->tickets->count());
    }

    /** @test */
    function a_customer_cannot_purchase_tickets_to_an_unpublished_event()
    {
        // Arrange - Create an unpublished event
        $event = Event::factory()->unpublished()->create();

        // Act - purchase tickets
        $response = $this->orderTickets($event, [
            'email' => 'todd@todd.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert
        // 404 response
        $response->assertStatus(404);

        // No orders were created
        $this->assertEquals(0, $event->orders()->count());

        // No charges are made
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        // Arrange - create an event
        $event = Event::factory()->published()->create();

        // Act - purchase tickets
        $response = $this->orderTickets($event, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert validation error
        $this->assertValidationError($response, 'email');
    }

    /** @test */
    function email_must_be_valid_to_purchase_tickets()
    {
        // Arrange - create an event
        $event = Event::factory()->published()->create();

        // Act - purchase tickets
        $response = $this->orderTickets($event, [
            'email' => 'todd',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert validation error
        $this->assertValidationError($response, 'email');
    }

    /** @test */
    function ticket_quantity_is_required_to_purchase_tickets()
    {
        // Arrange - create an event
        $event = Event::factory()->published()->create();

        // Act - purchase tickets
        $response = $this->orderTickets($event, [
            'email' => 'todd@todd.com',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert validation error
        $this->assertValidationError($response, 'ticket_quantity');
    }

    /** @test */
    function ticket_quantity_must_be_at_least_one_to_purchase_tickets()
    {
        // Arrange - create an event
        $event = Event::factory()->published()->create();

        // Act - purchase tickets
        $response = $this->orderTickets($event, [
            'email' => 'todd@todd.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        // Assert validation error
        $this->assertValidationError($response, 'ticket_quantity');
    }

    /** @test */
    function payment_token_is_required_to_purchase_event_tickets()
    {
        // Arrange - create an event
        $event = Event::factory()->published()->create();

        // Act - purchase tickets
        $response = $this->orderTickets($event, [
            'email' => 'todd@todd.com',
            'ticket_quantity' => 3
        ]);

        // Assert validation error
        $this->assertValidationError($response, 'payment_token');
    }

    /** @test */
    function an_order_is_not_created_if_payment_fails()
    {
        // Arrange - create an event
        $event = Event::factory()->published()->create([
            'ticket_price' => 2500
        ]);

        // Act - purchase tickets
        $response = $this->orderTickets($event, [
            'email' => 'todd@todd.com',
            'ticket_quantity' => 3,
            'payment_token' => 'INVALID_TOKEN'
        ]);

        // Assert
        // 422 HTTP response
        $response->assertStatus(422);

        // No order was created
        $order = $event->orders()->where('email', 'todd@todd.com')->first();
        $this->assertNull($order);
    }
}
