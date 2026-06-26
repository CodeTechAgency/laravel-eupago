<?php

namespace CodeTech\EuPago\Events;

use CodeTech\EuPago\Models\MbwayReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class MBWayReferencePaid
{
    use InteractsWithSockets, SerializesModels;

    /**
     * The MBWay reference object.
     *
     * @var MbwayReference
     */
    public $reference;

    /**
     * MBWayReferencePaid constructor.
     *
     * @param MbwayReference $reference
     */
    public function __construct(MbwayReference $reference)
    {
        $this->reference = $reference;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
