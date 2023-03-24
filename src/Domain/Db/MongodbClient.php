<?php

namespace App\Domain\Db;


use MongoDB\Client;
use MongoDB\Database;

class MongodbClient extends Client
{
    public static function create(
        string $mongodbUrl,
        string $mongodbDb,
    ) : Client
    {
        return new self(
            $mongodbUrl,
            [
                'serverSelectionTimeoutMS' => 5000,
                'connectTimeoutMS' => 10000,
                'readPreference' => 'primary',
                'w' => 'majority'
            ],
            [
                'typeMap' => [
                    'array' => 'array',
                    'document' => 'array',
                    'root' => 'array',
                ],
            ]
        );

    }
}