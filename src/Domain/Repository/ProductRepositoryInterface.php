<?php

namespace App\Domain\Repository;

use App\Application\ValueObject\ProductId;
use App\Document\Product;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;

    public function get(ProductId $productId): Product;

}