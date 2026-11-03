<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /** Customers may change only their own reviews. */
    public function modify(User $user, Review $review): bool
    {
        return $user->isCustomer() && $user->id === $review->user_id;
    }
}
