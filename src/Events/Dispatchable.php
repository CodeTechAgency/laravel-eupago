<?php

namespace CodeTech\EuPago\Events;

trait Dispatchable
{
    /**
     * Dispatch the event with the given arguments.
     *
     * @param  mixed  ...$arguments
     * @return array|null the listeners' responses
     */
    public static function dispatch(...$arguments)
    {
        return event(new static(...$arguments));
    }
}
