<?php

namespace CodeTech\EuPago\Traits;

use Carbon\Carbon;
use CodeTech\EuPago\MB\MB;
use CodeTech\EuPago\Models\MbReference;

trait Mbable
{
    use CreatesEuPagoReferences;

    /**
     * Get all of the model's MB references.
     */
    public function mbReferences()
    {
        return $this->morphMany(MbReference::class, 'mbable');
    }

    /**
     * Creates and persists an MB reference.
     *
     * @param float $value
     * @param string $id
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param float $minValue
     * @param float $maxValue
     * @param bool $allowDuplication
     * @return \Illuminate\Database\Eloquent\Model|array the persisted reference, or the errors on failure
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createMbReference(float $value, string $id, Carbon $startDate, Carbon $endDate, float $minValue, float $maxValue, bool $allowDuplication = false)
    {
        return $this->persistReference(
            new MB($value, $id, $startDate, $endDate, $minValue, $maxValue, $allowDuplication),
            'mbReferences'
        );
    }
}
