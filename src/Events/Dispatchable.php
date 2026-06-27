<?php

namespace CodeTech\EuPago\Events;

trait Dispatchable
{
    /**
     * Dispatch the event with the given arguments.
     *
     * @param  mixed  ...$arguments
     * @return mixed
     */
    public static function dispatch(...$arguments)
    {
        return event(new static(...$arguments));
    }
}
