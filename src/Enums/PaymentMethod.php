<?php

namespace CodeTech\EuPago\Enums;

/**
 * The payment methods the package handles, backed by the code Eupago's
 * notifications identify them with (`mp`).
 */
enum PaymentMethod: string
{
    case Multibanco = 'PC:PT';
    case MbWay = 'MW:PT';
    case PayShop = 'PS:PT';
    case PaysafeCard = 'PF:PT';
    case CreditCard = 'CC:PT';
}
