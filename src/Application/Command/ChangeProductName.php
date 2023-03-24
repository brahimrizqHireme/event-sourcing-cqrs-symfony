<?php

namespace App\Application\Command;

use App\Application\ValueObject\ProductId;
use App\Domain\CQRS\Command\CommandInterface;

final readonly class ChangeProductName implements CommandInterface
{

    public function __construct(
        private ProductId $id,
        private string $name
    )
    {

    }

    public function id(): ProductId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

}