<?php

namespace App\Modules\Ordering\Data;

/**
 * Tampilan keranjang terrevalidasi untuk satu pembeli. subtotal hanya menjumlah baris
 * yang masih dapat dipesan (available), sehingga tidak pernah menagih item bermasalah.
 * Semua nilai uang berupa integer Rupiah.
 */
final readonly class CartView
{
    /**
     * @param  list<CartLine>  $lines
     */
    public function __construct(
        public array $lines,
        public int $subtotal,
        public int $totalQuantity,
        public bool $hasBlockingIssues,
        public int $serviceFeeAmount = 0,
    ) {}

    /** UC-03 langkah 5: total keseluruhan termasuk biaya layanan. */
    public function grandTotal(): int
    {
        return $this->subtotal + $this->serviceFeeAmount;
    }

    /**
     * UC-03 langkah 4: baris dikelompokkan per toko (tenant) beserta subtotal toko tersebut.
     * Jumlah seluruh subtotal toko selalu sama dengan $subtotal.
     *
     * @return list<array{tenant_id: int, tenant_name: string, subtotal: int, lines: list<CartLine>}>
     */
    public function tenantGroups(): array
    {
        $groups = [];

        foreach ($this->lines as $line) {
            $groups[$line->tenantId] ??= [
                'tenant_id' => $line->tenantId,
                'tenant_name' => $line->tenantName,
                'subtotal' => 0,
                'lines' => [],
            ];
            $groups[$line->tenantId]['lines'][] = $line;
            $groups[$line->tenantId]['subtotal'] += $line->available ? $line->lineTotal : 0;
        }

        return array_values($groups);
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }

    /** Ada baris yang harganya berubah sejak dimasukkan (perlu persetujuan pembeli). */
    public function hasPriceChanges(): bool
    {
        foreach ($this->lines as $line) {
            if ($line->priceChanged()) {
                return true;
            }
        }

        return false;
    }

    /** Keranjang siap checkout: ada isi dan tidak ada baris bermasalah. */
    public function isOrderable(): bool
    {
        return ! $this->isEmpty() && ! $this->hasBlockingIssues;
    }
}
