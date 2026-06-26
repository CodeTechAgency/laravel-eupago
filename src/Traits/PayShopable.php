<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\Models\PayShopReference;

trait PayShopable
{
    /**
     * Get all of the model's PayShop references.
     */
    public function payShopReferences()
    {
        return $this->morphMany(PayShopReference::class, 'payshopable');
    }
}
