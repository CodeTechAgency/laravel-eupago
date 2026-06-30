<?php

namespace CodeTech\EuPago\Events;

use CodeTech\EuPago\Models\PaysafecardReference;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class PaysafecardReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The Paysafecard reference object.
     *
     * @var PaysafecardReference
     */
    public $reference;

    /**
     * PaysafecardReferencePaid constructor.
     */
    public function __construct(PaysafecardReference $reference)
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
