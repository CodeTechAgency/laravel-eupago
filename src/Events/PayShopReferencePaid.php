<?php

namespace CodeTech\EuPago\Events;

use CodeTech\EuPago\Models\PayShopReference;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class PayShopReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The PayShop reference object.
     *
     * @var PayShopReference
     */
    public $reference;

    /**
     * PayShopReferencePaid constructor.
     */
    public function __construct(PayShopReference $reference)
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
