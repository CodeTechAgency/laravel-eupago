<?php

namespace CodeTech\EuPago\Models;

use Illuminate\Database\Eloquent\Model;

class MbReference extends Model
{
    /**
     * @inheritdoc
     */
    protected $fillable = [
        'entity',
        'reference',
        'value',
        'start_date',
        'end_date',
        'min_value',
        'max_value',
        'state',
    ];

    /**
     * @inheritDoc
     */
    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];


    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scopes a query to only include paid references.
     *
     * @param $query
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
     * Get the owning mbable model.
     */
    public function mbable()
    {
        return $this->morphTo();
    }
}
