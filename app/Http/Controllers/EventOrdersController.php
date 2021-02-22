<?php

namespace App\Http\Controllers;

use App\Exceptions\NotEnoughTicketsException;
use App\Exceptions\PaymentFailedException;
use App\Services\Billing\PaymentGateway;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Order;
use App\Services\Reservation;

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
            $tickets = $event->findTickets($request['ticket_quantity']);
            $reservation = new Reservation($tickets);

            $this->paymentGateway->charge(
                $reservation->totalCost(),
                $request['payment_token']
            );

            $order = Order::forTickets(
                $tickets,
                request('email'),
                $reservation->totalCost()
            );

            return response()->json($order, 201);
        } catch (PaymentFailedException $e) {
            return response()->json($e, 422);
        } catch (NotEnoughTicketsException $e) {
            return response()->json($e, 422);
        }
    }
}
