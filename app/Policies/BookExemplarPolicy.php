<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\BookExemplar;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookExemplarPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === RoleEnum::ADMIN || $user->role === RoleEnum::STAFF;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BookExemplar $bookExemplar): bool
    {
        return $user->role === RoleEnum::ADMIN || $user->role === RoleEnum::STAFF;
    }
}
