<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'external_id',
        'gateway',
        'status',
        'amount',
        'fees',
        'net_amount',
        'paid_at',
        'raw_payload',
        'normalized_payload',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'raw_payload'        => 'array',
            'normalized_payload' => 'array',
            'amount'             => 'decimal:2',
            'fees'               => 'decimal:2',
            'net_amount'         => 'decimal:2',
            'paid_at'            => 'datetime',
        ];
    }

    /**
     * Pedido associado ao pagamento.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Verifica se o pagamento foi bem-sucedido.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'paid';
    }
}
