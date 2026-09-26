<?php

namespace CodeTech\EuPago\Models;

use CodeTech\EuPago\Enums\ReferenceState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaysafeCardReference extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'paysafecard_references';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'identifier',
        'reference',
        'transaction_id',
        'url',
        'value',
        'state',
    ];

    protected $casts = [
        'value' => 'float',
        'state' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scopes a query to only include paid references.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePaid($query)
    {
        return $query->where('state', ReferenceState::Paid->value);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the owning paysafecardable model.
     */
    public function paysafecardable()
    {
        return $this->morphTo();
    }
}
