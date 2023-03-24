<?php
declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\CQRS\Command\CommandHandlerInterface;
use App\Domain\Repository\ProductRepositoryInterface;

final class ChangeProductNameHandler implements CommandHandlerInterface
{

    public function __construct(
        private ProductRepositoryInterface $productRepository
    )
    {
    }

    public function __invoke(ChangeProductName $command): void
    {
        //todo add checks
        $product = $this->productRepository->get($command->id());
        $product->changeProductName($command->name());
        $this->productRepository->save($product);
//        $this->productRepository->save($product);
    }
}