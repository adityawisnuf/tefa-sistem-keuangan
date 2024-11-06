<?php

namespace App\Policies;

use App\Models\KantinProduk;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProdukPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KantinProduk $kantinProduk): bool
    {
        return $user->usaha->first()->id === $kantinProduk->usaha_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KantinProduk $kantinProduk): bool
    {
        return $user->usaha->id === $kantinProduk->usaha_id;
    }
}
