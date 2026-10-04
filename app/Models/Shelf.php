<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $code
 */
#[Fillable(['code'])]
class Shelf extends Model
{
    use HasFactory;
    
    public function books(): HasMany {
        return $this->hasMany(Book::class);
    }
}
