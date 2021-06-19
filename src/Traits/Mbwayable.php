<?php

namespace CodeTech\EuPago\Traits;

use EuPago\Models\MbwayReference;

trait Mbwayable
{
    /**
     * Get all of the models's MB Way references.
     */
    public function mbwayReferences()
    {
        return $this->morphMany(MbwayReference::class, 'mbwayable');
    }
}
