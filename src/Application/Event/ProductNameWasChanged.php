<?php

namespace App\Application\Event;

use App\Application\ValueObject\ProductId;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Serialization\SerializablePayload;

class ProductNameWasChanged implements SerializablePayload
{
    public function __construct(
        private string $aggregateRootId,
        private string $name
    )
    {
    }

    /**
     * @return string
     */
    public function getAggregateRootId(): string
    {
        return $this->aggregateRootId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    public function toPayload(): array
    {
        return [
            'id' => $this->aggregateRootId,
            'name' => $this->name,
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new self(
            $payload['id'],
            $payload['name'],
        );
    }
}
