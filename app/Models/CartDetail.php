<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $book_exemplar_id
 */
#[Fillable(['cart_id', 'book_exemplar_id'])]
class CartDetail extends Model
{
    public function cart(): BelongsTo {
        return $this->belongsTo(Cart::class);
    }

    public function exemplar(): BelongsTo {
        return $this->belongsTo(BookExemplar::class);
    }
}
