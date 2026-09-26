<?php

namespace CodeTech\EuPago\Models;

use CodeTech\EuPago\Enums\ReferenceState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MbwayReference extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'reference',
        'transaction_id',
        'value',
        'alias',
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
     * Get the owning mbwayable model.
     */
    public function mbwayable()
    {
        return $this->morphTo();
    }
}
