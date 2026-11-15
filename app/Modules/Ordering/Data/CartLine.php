<?php

namespace App\Modules\Ordering\Data;

/**
 * Baris keranjang HASIL REVALIDASI. Harga (unitPrice, lineTotal), stok maksimum, dan
 * status ketersediaan selalu dihitung ulang dari basis data saat menyusun tampilan —
 * bukan dari nilai yang tersimpan di Redis. priceAtAdd hanya informatif (deteksi
 * "harga berubah"), tidak pernah dipakai sebagai otoritas.
 *
 * Satu buku = satu baris (tidak ada modifier seperti pada kantin), sehingga identitas
 * baris adalah bookId.
 */
final readonly class CartLine
{
    /**
     * @param  list<string>  $issues  book_unavailable | insufficient_stock | price_changed
     */
    public function __construct(
        public int $bookId,
        public int $tenantId,
        public string $tenantName,
        public string $title,
        public ?string $author,
        public string $format,
        public ?string $coverPath,
        public int $quantity,
        public int $unitPrice,
        public int $priceAtAdd,
        public int $maxQuantity,
        public int $lineTotal,
        public bool $available,
        public array $issues,
    ) {}

    public function hasIssue(string $issue): bool
    {
        return in_array($issue, $this->issues, true);
    }

    public function priceChanged(): bool
    {
        return $this->hasIssue('price_changed');
    }
}
