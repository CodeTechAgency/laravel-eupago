<?php

namespace CodeTech\EuPago\Models;

use Illuminate\Database\Eloquent\Model;

class PaysafecardReference extends Model
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
     * Get the owning paysafecardable model.
     */
    public function paysafecardable()
    {
        return $this->morphTo();
    }
}
