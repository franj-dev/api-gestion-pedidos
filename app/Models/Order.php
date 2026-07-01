<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // Campos que permitimos rellenar desde los formularios/APIs de golpe (Mass Assignment)
    protected $fillable = ['user_id', 'total', 'status'];

    public function user(): BelongsTo
    {
        // Un pedido pertenece a un usuario
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        // Un pedido tiene muchos items
        return $this->hasMany(OrderItem::class);
    }
}