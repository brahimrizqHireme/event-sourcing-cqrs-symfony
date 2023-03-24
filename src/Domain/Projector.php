<?php

namespace App\Domain;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageConsumer;

abstract class Projector implements MessageConsumer
{
    public function handle(Message $message): void
    {
        $event = $message->payload();
        $className = get_class($event);
        $this->{'on' . substr($className, (strrpos($className, '\\') ?: -1) + 1)}($event);
    }
}