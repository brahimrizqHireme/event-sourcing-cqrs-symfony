<?php

namespace App\Infra\ServiceFactory;


use App\Document\Product;
use App\Domain\Db\MongodbClient;
use App\Infra\MessageRepository\MongoDBMessageRepository;
use App\Infra\Projector\ProductProjector;
use App\Infra\Repository\ProductRepository;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;
use EventSauce\MessageRepository\DoctrineMessageRepository\DoctrineUuidV4MessageRepository;
use MongoDB\Driver\Manager;

class ProductRepositoryFactory
{
    public function __construct(
        private MongodbClient $client
    )
    {

    }

    public function create(): ProductRepository
    {
        return new ProductRepository(
            new EventSourcedAggregateRootRepository(
                Product::class,
                new MongoDBMessageRepository($this->client),
                new SynchronousMessageDispatcher(
                    new ProductProjector(),
                )
            )
        );
    }
}