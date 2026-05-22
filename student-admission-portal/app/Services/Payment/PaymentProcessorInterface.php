<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Application;
use App\Models\Payment;

interface PaymentProcessorInterface
{
    /**
     * Process the payment for the application.
     */
    public function process(Application $application, array $data): Payment;
}
