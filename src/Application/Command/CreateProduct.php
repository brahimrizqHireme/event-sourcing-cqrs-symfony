<?php

namespace App\Application\Command;

use App\Application\ValueObject\ProductId;
use App\Domain\CQRS\Command\CommandInterface;

final readonly class CreateProduct implements CommandInterface
{

    public function __construct(
        private ProductId $id,
        private string $name,
        private string $description,
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
    public function description(): string
    {
        return $this->description;
    }
}