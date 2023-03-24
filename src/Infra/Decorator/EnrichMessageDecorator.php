<?php

namespace App\Infra\Decorator;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;

class EnrichMessageDecorator implements MessageDecorator
{
    public function decorate(Message $message): Message
    {
        return $message->withHeader('x-decorated-by', 'test decorator');
    }
}