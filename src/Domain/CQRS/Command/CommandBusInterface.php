<?php

namespace App\Domain\CQRS\Command;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): void;
}