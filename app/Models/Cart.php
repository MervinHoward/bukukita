<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $loaner_id
 */
#[Fillable(['loaner_id'])]
class Cart extends Model
{
    public function details(): HasMany {
        return $this->hasMany(CartDetail::class);
    }

    public function loaner(): BelongsTo {
        return $this->belongsTo(User::class, 'loaner_id');
    }
}
