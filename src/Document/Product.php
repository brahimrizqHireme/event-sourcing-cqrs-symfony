<?php

namespace App\Document;

use App\Application\Event\ProductWasCreated;
use App\Application\ValueObject\ProductId;
use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use Symfony\Component\Uid\Uuid;

class Product implements AggregateRoot
{
    use AggregateRootBehaviour;

    private string $id;
    private string $name;
    private string $description;

    public static function createProduct(
        ProductId $id,
        string $name,
        string $description
    ): Product
    {
        $product = new static($id->toString());
        $product->recordThat(new ProductWasCreated($id, $name, $description));

        return $product;
    }

    public function applyProductWasCreated(ProductWasCreated $event)
    {
        $this->aggregateRootId = $event->getId();
        $this->id = $event->getId()->toString();
        $this->name = $event->getName();
        $this->description = $event->getDescription();
    }

    /**
     * @return mixed
     */
    public function getId()
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
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


}