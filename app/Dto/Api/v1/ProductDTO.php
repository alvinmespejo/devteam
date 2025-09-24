<?php

namespace App\Dto\Api\v1;

class ProductDTO
{
    /**
     * Undocumented function
     *
     * @param integer|null $id
     * @param integer|null $externalId
     * @param string $title
     * @param float $price
     * @param string $description
     * @param string $category
     * @param string $image
     * @param string|float $ratingRate
     * @param string|integer $ratingCount
     */
    public function __construct(
        public readonly ?int $id,
        public readonly int $externalId,
        public readonly string $title,
        public readonly float $price,
        public readonly string $description,
        public readonly string $category,
        public readonly string $image,
        public readonly string|float $ratingRate,
        public readonly string|int $ratingCount,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {
    }
}
