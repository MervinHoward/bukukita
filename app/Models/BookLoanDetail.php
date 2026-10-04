<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $book_loan_id
 * @property int $book_exemplar_id
 */
#[Fillable(['book_loan_id', 'book_exemplar_id'])]
class BookLoanDetail extends Model
{
    public function bookLoan(): BelongsTo {
        return $this->belongsTo(BookLoan::class);
    }

    public function bookExemplar(): BelongsTo {
        return $this->belongsTo(BookExemplar::class);
    }
}
