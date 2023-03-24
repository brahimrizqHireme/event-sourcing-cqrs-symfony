<?php

namespace App\Infra\Projector;

use App\Application\Event\ProductWasCreated;
use App\Domain\Projector;

class ProductProjector extends Projector
{

    public function onProductWasCreated(ProductWasCreated $event)
    {
        //todo do stuff
        //send email, notification, update something

        dump('projected');
    }

}