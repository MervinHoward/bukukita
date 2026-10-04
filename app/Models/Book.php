<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property int $author_id
 * @property int $publisher_id
 * @property Carbon $publication_year
 * @property int $shelf_id
 * @property string|null $description
 * @property string $isbn
 * @property string|null $physical_description
 */
#[Fillable(['title', 'author_id', 'publisher_id', 'publication_year', 'shelf_id', 'description', 'isbn', 'physical_description'])]
class Book extends Model
{
    use HasFactory;

    public function author(): BelongsTo {
        return $this->belongsTo(Author::class);
    }

    public function publisher(): BelongsTo {
        return $this->belongsTo(Publisher::class);
    }

    public function shelf(): BelongsTo {
        return $this->belongsTo(Shelf::class);
    }

    public function categories(): BelongsToMany {
        return $this->belongsToMany(Category::class, 'book_categories');
    }

    public function exemplars(): HasMany {
        return $this->hasMany(BookExemplar::class);
    }
}
