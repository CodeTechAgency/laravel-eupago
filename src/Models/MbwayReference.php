<?php

namespace CodeTech\EuPago\Models;

use Illuminate\Database\Eloquent\Model;

class MbwayReference extends Model
{
    /**
     * @inheritdoc
     */
    protected $fillable = [
        'reference',
        'value',
        'alias',
        'state',
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
     * Get the owning mbwayable model.
     */
    public function mbwayable()
    {
        return $this->morphTo();
    }
}
