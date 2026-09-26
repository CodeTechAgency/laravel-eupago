<?php

namespace CodeTech\EuPago\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MbReferencePayment extends Model
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'transaction_id',
        'value',
    ];

    protected $casts = [
        'value' => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the paid MB reference.
     *
     * @return BelongsTo<MbReference, $this>
     */
    public function reference(): BelongsTo
    {
        return $this->belongsTo(MbReference::class, 'mb_reference_id');
    }
}
