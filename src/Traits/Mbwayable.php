<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\MBWay\MBWay;
use CodeTech\EuPago\Models\MbwayReference;

trait Mbwayable
{
    use CreatesEuPagoReferences;

    /**
     * Get all of the models' MB Way references.
     */
    public function mbwayReferences()
    {
        return $this->morphMany(MbwayReference::class, 'mbwayable');
    }

    /**
     * Creates and persists an MB Way reference.
     *
     * @param float $value
     * @param int $id
     * @param string $alias
     * @param string|null $description
     * @return \Illuminate\Database\Eloquent\Model|array the persisted reference, or the errors on failure
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createMbwayReference(float $value, int $id, string $alias, ?string $description = null)
    {
        return $this->persistReference(
            new MBWay($value, $id, $alias, $description),
            'mbwayReferences'
        );
    }
}
