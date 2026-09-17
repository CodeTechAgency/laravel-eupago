<?php

namespace CodeTech\EuPago\Models;

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
     * Get the owning creditcardable model.
     */
    public function creditcardable()
    {
        return $this->morphTo();
    }
}
