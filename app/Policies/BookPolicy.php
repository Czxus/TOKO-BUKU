<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    private function ownsBook(User $user, Book $book): bool
    {
        return $user->isSellerAdmin()
            && $user->tenant_id !== null
            && (int) $user->tenant_id === (int) $book->tenant_id;
    }

    public function update(User $user, Book $book): bool
    {
        return $this->ownsBook($user, $book);
    }

    public function delete(User $user, Book $book): bool
    {
        return $this->ownsBook($user, $book);
    }
}
