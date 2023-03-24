<?php

namespace App\Infra\CQRS;

use App\Domain\CQRS\Command\CommandBusInterface;
use App\Domain\CQRS\Command\CommandInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerCommandBus implements CommandBusInterface
{
    private MessageBusInterface $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function dispatch(CommandInterface $command): void
    {
        $this->commandBus->dispatch($command);
    }
}