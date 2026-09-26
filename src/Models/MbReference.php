<?php

namespace CodeTech\EuPago\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MbReference extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'entity',
        'reference',
        'transaction_id',
        'value',
        'start_date',
        'end_date',
        'min_value',
        'max_value',
        'state',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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

    /**
     * Scopes a query to the references that accept a payment of the given
     * value: their own value, or any amount within their range.
     *
     * @return mixed
     */
    public function scopeAccepting($query, $value)
    {
        return $query->where(function ($query) use ($value) {
            $query->where('value', $value)
                ->orWhere(fn ($query) => $query->where('min_value', '<=', $value)->where('max_value', '>=', $value));
        });
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

    /**
     * Get the payments made to the reference.
     *
     * @return HasMany<MbReferencePayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(MbReferencePayment::class);
    }
}
