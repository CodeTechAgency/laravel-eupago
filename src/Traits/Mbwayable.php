<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\MBWay\MBWay;
use CodeTech\EuPago\Models\MbwayReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

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
     * @return Model|array the persisted reference, or the errors on failure
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function createMbwayReference(float $value, int $id, string $alias, ?string $description = null)
    {
        return $this->persistReference(
            new MBWay($value, $id, $alias, $description),
            'mbwayReferences'
        );
    }
}
