<?php

namespace App\Infra\CQRS;

use App\Domain\CQRS\Query\QueryBusInterface;
use App\Domain\CQRS\Query\QueryInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerQueryBus implements QueryBusInterface
{
    use HandleTrait {
        handle as handleQuery;
    }

    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    /** @return mixed */
    public function handle(QueryInterface $query)
    {
        return $this->handleQuery($query);
    }
}