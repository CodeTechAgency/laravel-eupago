<?php

namespace CodeTech\EuPago\Events;

use CodeTech\EuPago\Models\CreditCardReference;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class CreditCardReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The Credit Card reference object.
     *
     * @var CreditCardReference
     */
    public $reference;

    /**
     * CreditCardReferencePaid constructor.
     */
    public function __construct(CreditCardReference $reference)
    {
        $this->reference = $reference;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
