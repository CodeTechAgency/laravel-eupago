<?php

namespace CodeTech\EuPago\Models;

use CodeTech\EuPago\Enums\ReferenceState;
use Illuminate\Database\Eloquent\Builder;
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
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopePaid($query)
    {
        return $query->where('state', ReferenceState::Paid->value);
    }

    /**
     * Scopes a query to the references that accept a payment of the given
     * value: their own value, or any amount within their range.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeAccepting(Builder $query, string $value): Builder
    {
        return $query->where(function (Builder $query) use ($value): void {
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
