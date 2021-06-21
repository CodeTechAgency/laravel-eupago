<?php

namespace CodeTech\EuPago\Traits;

use Carbon\Carbon;
use CodeTech\EuPago\MB\MB;
use CodeTech\EuPago\Models\MbReference;
use CodeTech\EuPago\Models\MbwayReference;

trait Mbable
{
    /**
     * Get all of the model's MB references.
     */
    public function mbReferences()
    {
        return $this->morphMany(MbReference::class, 'mbable');
    }

    /**
     * Creates a MB reference.
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createMbReference(float $value, int $id, Carbon $startDate, Carbon $endDate, float $minValue, float $maxValue, bool $allowDuplication = false)
    {
        $mb = new MB(
            $value,
            $id,
            $startDate,
            $endDate,
            $minValue,
            $maxValue,
            $allowDuplication
        );

        try {
            $mbReferenceData = $mb->create();
        } catch (\Exception $e) {
            throw $e;
        }

        if ($mb->hasErrors()) {
            return $mb->getErrors();
        }

        $this->mbReferences()->create($mbReferenceData);
    }
}
