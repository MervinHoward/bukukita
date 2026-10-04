<?php

namespace App\Models;

use App\Enums\LoanStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $loaner_id
 * @property Carbon $loan_time
 * @property Carbon $due_loan_time
 * @property LoanStatusEnum $loan_status
 * @property int $id
 */
#[Fillable(['loaner_id', 'loan_time', 'due_loan_time', 'loan_status', 'staff_id'])]
class BookLoan extends Model
{
    public function loaner(): BelongsTo {
        return $this->belongsTo(User::class, 'loaner_id');
    }

    public function staff(): BelongsTo {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function details(): HasMany {
        return $this->hasMany(BookLoanDetail::class);
    }

    public function return(): HasOne {
        return $this->hasOne(BookReturn::class);
    }
}
