<?php

namespace App\Infra\ServiceFactory;


use App\Document\Product;
use App\Domain\Db\MongodbClient;
use App\Infra\AggregateRoot\EventAggregateRootRepository;
use App\Infra\Decorator\EnrichMessageDecorator;
use App\Infra\MessageRepository\MongoDBMessageRepository;
use App\Infra\Projector\ProductProjector;
use App\Infra\Repository\ProductRepository;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use EventSauce\EventSourcing\DefaultHeadersDecorator;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDecoratorChain;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\SynchronousMessageDispatcher;

class ProductRepositoryFactory
{
    public function __construct(
        private MongodbClient $client
    )
    {

    }

    public function create(): ProductRepository
    {

        $decoratorChain = new MessageDecoratorChain(
            new DefaultHeadersDecorator(),
            new EnrichMessageDecorator()
        );

        return new ProductRepository(
            new EventAggregateRootRepository(
                Product::class,
                new MongoDBMessageRepository($this->client),
                new SynchronousMessageDispatcher(
                    new ProductProjector(),
                ),
                $decoratorChain
            )
        );
    }
}