<?php

namespace CodeTech\EuPago\Events;

use CodeTech\EuPago\Models\MbReference;
use CodeTech\EuPago\Models\MbReferencePayment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
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
     * The payment that was made — a reference that allows repeat payments,
     * or accepts an amount range, can be paid more than once and for
     * amounts other than its value.
     *
     * @var MbReferencePayment|null
     */
    public $payment;

    /**
     * MBReferencePaid constructor.
     */
    public function __construct(MbReference $reference, ?MbReferencePayment $payment = null)
    {
        $this->reference = $reference;
        $this->payment = $payment;
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
