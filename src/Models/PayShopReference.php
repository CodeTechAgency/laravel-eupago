<?php

namespace CodeTech\EuPago\Models;

use CodeTech\EuPago\Enums\ReferenceState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PayShopReference extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'payshop_references';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'reference',
        'transaction_id',
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
     * Get the owning payshopable model.
     */
    public function payshopable()
    {
        return $this->morphTo();
    }
}
