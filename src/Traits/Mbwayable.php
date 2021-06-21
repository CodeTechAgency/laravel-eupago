<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\Models\MbwayReference;

trait Mbwayable
{
    /**
     * Get all of the models' MB Way references.
     */
    public function mbwayReferences()
    {
        return $this->morphMany(MbwayReference::class, 'mbwayable');
    }
}
