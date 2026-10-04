<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $first_name
 * @property string|null $middle_name
 * @property string $last_name
 * @property string $email
 * @property string|null $bio
 */
#[UseFactory(AuthorFactory::class)]
#[Fillable(['first_name', 'middle_name', 'last_name', 'email', 'bio'])]
class Author extends Model
{
    use HasFactory;

    public function books(): HasMany {
        return $this->hasMany(Book::class);
    }

    public function firstName(): Attribute {
        return Attribute::make(
            set: fn (string $value) => ucwords(strtolower(trim($value)))
        );
    }

    public function middleName(): Attribute {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? ucwords(strtolower(trim($value))) : null
        );
    }

    public function lastName(): Attribute {
        return Attribute::make(
            set: fn (string $value) => ucwords(strtolower(trim($value)))
        );
    }

    public function bio(): Attribute {
        return Attribute::make(
            set: fn (?string $value) => $value !== null ? ucfirst(strtolower(trim($value))) : null
        );
    }
}
