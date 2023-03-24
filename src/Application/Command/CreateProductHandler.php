<?php
declare(strict_types=1);

namespace App\Application\Command;

use App\Application\ValueObject\ProductId;
use App\Document\Product;
use App\Domain\CQRS\Command\CommandHandlerInterface;
use App\Domain\Repository\ProductRepositoryInterface;

final class CreateProductHandler implements CommandHandlerInterface
{

    public function __construct(
        private ProductRepositoryInterface $productRepository
    )
    {
    }

    public function __invoke(CreateProduct $command): void
    {

        //todo add check business
        $product = Product::createProduct(
            $command->id(),
            $command->name(),
            $command->description()
            );

        $this->productRepository->save($product);
    }
}