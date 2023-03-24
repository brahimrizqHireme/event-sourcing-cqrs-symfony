<?php

namespace App\Domain\CQRS\Query;

interface QueryBusInterface
{

    /** @return mixed */
    public function handle(QueryInterface $query);
}