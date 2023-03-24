<?php

namespace App\Domain\Repository;

use App\Document\Product;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;

    public function get(string $productId): Product;

}