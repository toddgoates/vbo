<?php

namespace App\Http\Controllers;

use App\Exceptions\NotEnoughTicketsException;
use App\Exceptions\PaymentFailedException;
use App\Services\Billing\PaymentGateway;
use Illuminate\Http\Request;
use App\Models\Event;

class EventOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($id, Request $request)
    {
        $event = Event::published()->findOrFail($id);

        $request->validate([
            'email' => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'payment_token' => ['required']
        ]);

        try {
            /**
             * TODO: Redesign flow
             * This will be done in the next module
             * 
             * Find some tickets
             * Charge the customer for them
             * Create an order if charge is successful
             */

            $order = $event->orderTickets($request['email'], $request['ticket_quantity']);
            $this->paymentGateway->charge(
                $request['ticket_quantity'] * $event->ticket_price,
                $request['payment_token']
            );

            return response()->json($order, 201);
        } catch (PaymentFailedException $e) {
            $order->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], 422);
        }
    }
}
