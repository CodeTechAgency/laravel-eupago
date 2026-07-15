<?php

namespace CodeTech\EuPago\Events;

use CodeTech\EuPago\Models\PaysafeCardReference;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class PaysafeCardReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The PaysafeCard reference object.
     *
     * @var PaysafeCardReference
     */
    public $reference;

    /**
     * PaysafeCardReferencePaid constructor.
     */
    public function __construct(PaysafeCardReference $reference)
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
