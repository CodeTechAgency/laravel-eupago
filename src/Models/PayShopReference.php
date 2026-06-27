<?php

namespace CodeTech\EuPago\Models;

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
     * @return mixed
     */
    public function scopePaid($query)
    {
        return $query->where('state', 1);
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
