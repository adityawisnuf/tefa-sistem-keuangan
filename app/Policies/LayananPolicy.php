<?php

namespace App\Policies;

use App\Models\LaundryLayanan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LayananPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LaundryLayanan $laundryLayanan): bool
    {
        return $user->usaha->id === $laundryLayanan->usaha_id;
    }
    
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LaundryLayanan $laundryLayanan): bool
    {
        return $user->usaha->id === $laundryLayanan->usaha_id;
    }
}
