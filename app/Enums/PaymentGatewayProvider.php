<?php

namespace App\Enums;

enum PaymentGatewayProvider: string
{
    case Xendit = 'Xendit';
    case Midtrans = 'Midtrans';
    case Finpay = 'Finpay';
    case ManualBankTransfer = 'ManualBankTransfer';
}
