<?php

namespace App\Policies;

use App\Models\CartDetail;
use App\Models\User;

class CartDetailPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CartDetail $cartDetail): bool
    {
        return $cartDetail->cart->loaner_id === $user->id;
    }
}
