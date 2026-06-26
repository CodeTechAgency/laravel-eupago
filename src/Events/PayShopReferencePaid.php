<?php

namespace CodeTech\EuPago\Events;

use CodeTech\EuPago\Models\PayShopReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class PayShopReferencePaid
{
    use InteractsWithSockets, SerializesModels;

    /**
     * The PayShop reference object.
     *
     * @var PayShopReference
     */
    public $reference;

    /**
     * PayShopReferencePaid constructor.
     *
     * @param PayShopReference $reference
     */
    public function __construct(PayShopReference $reference)
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
