<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 */
#[Fillable(['name', 'address', 'phone', 'email'])]
class Publisher extends Model
{
    use HasFactory;
    
    public function books(): HasMany {
        return $this->hasMany(Book::class);
    }
}
