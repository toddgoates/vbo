<?php

namespace App\Services\Billing;

interface PaymentGateway
{

    public function charge($amount, $token);

}