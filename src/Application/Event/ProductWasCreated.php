<?php

namespace App\Application\Event;

use App\Application\ValueObject\ProductId;
use EventSauce\EventSourcing\Serialization\SerializablePayload;

class ProductWasCreated implements SerializablePayload
{
    public function __construct(
        private ProductId $id,
        private string $name,
        private string $description
    )
    {
    }

    /**
     * @return ProductId
     */
    public function getId(): ProductId
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    public function toPayload(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new self(
            $payload['id'],
            $payload['name'],
            $payload['description'],
        );
    }
}
