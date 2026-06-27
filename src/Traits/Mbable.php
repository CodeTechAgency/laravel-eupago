<?php

namespace CodeTech\EuPago\Traits;

use Carbon\Carbon;
use CodeTech\EuPago\MB\MB;
use CodeTech\EuPago\Models\MbReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

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
     * @return Model|array the persisted reference, or the errors on failure
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function createMbReference(float $value, string $id, Carbon $startDate, Carbon $endDate, float $minValue, float $maxValue, bool $allowDuplication = false)
    {
        return $this->persistReference(
            new MB($value, $id, $startDate, $endDate, $minValue, $maxValue, $allowDuplication),
            'mbReferences'
        );
    }
}
