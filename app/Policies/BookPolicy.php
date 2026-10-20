<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use App\Support\Tenancy\TenantContext;

class BookPolicy
{
    /**
     * Memastikan user hanya bisa melihat/mengubah buku milik tenant-nya sendiri.
     */
    public function update(User $user, Book $book): bool
    {
        $tenantContext = app(TenantContext::class);

        // Harus terikat dengan tenant yang aktif di konteks
        return $tenantContext->has() && $book->tenant_id === $tenantContext->id();
    }

    public function delete(User $user, Book $book): bool
    {
        return $this->update($user, $book);
    }
}