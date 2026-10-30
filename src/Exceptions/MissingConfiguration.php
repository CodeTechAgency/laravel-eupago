<?php

namespace CodeTech\EuPago\Exceptions;

use RuntimeException;

class MissingConfiguration extends RuntimeException
{
    /**
     * A setting the package needs is blank.
     */
    public static function forSetting(string $key, string $variable): self
    {
        return new self("The eupago.{$key} setting is blank. Set {$variable} to the value shown in the Eupago backoffice.");
    }
}
