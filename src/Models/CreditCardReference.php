<?php

namespace CodeTech\EuPago\Models;

use CodeTech\EuPago\Enums\ReferenceState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CreditCardReference extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $table = 'credit_card_references';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'identifier',
        'form_transaction_id',
        'transaction_id',
        'reference',
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
     * Get the owning creditcardable model.
     */
    public function creditcardable()
    {
        return $this->morphTo();
    }
}
