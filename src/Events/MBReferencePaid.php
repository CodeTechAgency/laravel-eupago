<?php

namespace CodeTech\EuPago\Events;

use CodeTech\EuPago\Models\MbReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MBReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The MbReference reference object.
     *
     * @var MbReference
     */
    public $reference;

    /**
     * MBReferencePaid constructor.
     *
     * @param MbReference $reference
     */
    public function __construct(MbReference $reference)
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
