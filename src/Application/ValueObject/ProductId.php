<?php

namespace App\Application\ValueObject;


use EventSauce\EventSourcing\AggregateRootId;
use Symfony\Component\Uid\Uuid;

class ProductId implements AggregateRootId
{
    private $id;

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public function toString(): string
    {
        return $this->id;
    }
    public static function generate(): self
    {
        return new self(Uuid::v4()->__toString());
    }

    public static function fromString(string $aggregateRootId): static
    {
        return new self($aggregateRootId);
    }
}