<?php

namespace App\Infra\Repository;

use App\Application\ValueObject\ProductId;
use App\Document\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use EventSauce\EventSourcing\AggregateRootRepository;
use function assert;

class ProductRepository implements ProductRepositoryInterface
{
    private AggregateRootRepository $repository;

    public function __construct(AggregateRootRepository $repository)
    {
        $this->repository = $repository;
    }
    public function save(Product $product): void
    {
        $this->repository->persist($product);
    }

    public function get(string $productId): Product
    {
        $tab = $this->repository->retrieve(ProductId::fromString($productId));
        assert($tab instanceof Product);

        return $tab;
    }
}