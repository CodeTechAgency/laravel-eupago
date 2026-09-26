<?php

namespace CodeTech\EuPago\Enums;

/**
 * The payment state stored on a reference's `state` column.
 */
enum ReferenceState: int
{
    case Pending = 0;
    case Paid = 1;
}
