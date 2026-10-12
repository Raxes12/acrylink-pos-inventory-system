<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->isAdmin()
            || ($user->isCashier() && (int) $transaction->cashier_id === (int) $user->id);
    }

    public function refund(User $user, Transaction $transaction): bool
    {
        return $this->view($user, $transaction);
    }

    public function void(User $user, Transaction $transaction): bool
    {
        return $this->view($user, $transaction);
    }
}
