<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $book_loan_id
 * @property int $staff_id
 * @property Carbon $return_time
 * @property int $overdue_days
 * @property float $overdue_fine
 */
#[Fillable(['book_loan_id', 'staff_id', 'return_time', 'overdue_days', 'overdue_fine'])]
class BookReturn extends Model
{
    public function bookLoan(): BelongsTo {
        return $this->belongsTo(BookLoan::class);
    }

    public function staff(): BelongsTo {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
