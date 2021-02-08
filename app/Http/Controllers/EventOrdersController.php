<?php

namespace App\Http\Controllers;

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
            $this->paymentGateway->charge(
                $request['ticket_quantity'] * $event->ticket_price,
                $request['payment_token']
            );

            $order = $event->orderTickets($request['email'], $request['ticket_quantity']);
        } catch (PaymentFailedException $e) {
            return response()->json([], 422);
        }


        return response()->json([], 201);
    }
}
