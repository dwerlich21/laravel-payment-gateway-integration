<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status do pedido
     */
    const STATUS_PENDING = 'pending';

    const STATUS_PAID = 'paid';

    const STATUS_FAILED = 'failed';

    const STATUS_REFUNDED = 'refunded';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'total_amount',
        'status',
        'gateway',
        'payment_method',
        'customer_name',
        'customer_email',
        'customer_cpf_cnpj',
        'customer_phone',
        'external_id',
        'payment_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    /**
     * Usuário dono do pedido.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Produto do pedido.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Pagamentos do pedido.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope para pedidos pendentes.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para pedidos pagos.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Verifica se o pedido está pago.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}
