<?php

namespace App\Infra\MessageRepository;


use App\Domain\Db\MongodbClient;
use App\Infra\Database\Database;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\OffsetCursor;
use EventSauce\EventSourcing\PaginationCursor;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\MessageSerializer;
use EventSauce\EventSourcing\UnableToPersistMessages;
use EventSauce\EventSourcing\UnableToRetrieveMessages;
use EventSauce\MessageRepository\TableSchema\DefaultTableSchema;
use EventSauce\MessageRepository\TableSchema\TableSchema;
use EventSauce\UuidEncoding\BinaryUuidEncoder;
use EventSauce\UuidEncoding\StringUuidEncoder;
use EventSauce\UuidEncoding\UuidEncoder;
use Generator;
use LogicException;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\WriteConcern;
use Ramsey\Uuid\Uuid;
use Throwable;

use function array_keys;
use function array_map;
use function array_merge;
use function count;
use function get_class;
use function implode;
use function json_decode;
use function json_encode;
use function sprintf;
use function var_dump;

class MongoDBMessageRepository implements MessageRepository
{
    private const SORT_ASCENDING = 1;
    private MessageSerializer $serializer;
    private TableSchema $tableSchema;
    private UuidEncoder $uuidEncoder;
    private int $jsonEncodeOptions;
    private string $tableName = 'event_stream';

    public function __construct(
        private ?MongodbClient $mongoClient
    )
    {
        $this->serializer = new ConstructingMessageSerializer();
        $this->tableSchema = new DefaultTableSchema();
        $this->uuidEncoder = new StringUuidEncoder();
        $this->jsonEncodeOptions = 0;
    }

    /**
     * @return Collection
     */
    public function getCollection()
    {
        return $this->mongoClient->selectDatabase(Database::DATA_BASE_SELECTED)
            ->selectCollection($this->tableName);
    }

    public function persist(Message ...$messages): void
    {
        if (count($messages) === 0) {
            return;
        }

        $documents = [];
        foreach ($messages as $index => $message) {
            $payload = $this->serializer->serializeMessage($message);
            $payload['headers'][Header::EVENT_ID] ??= \Symfony\Component\Uid\Uuid::v4()->__toString();

            $document = [
                    '_id' => $this->uuidEncoder->encodeString($payload['headers'][Header::EVENT_ID]),
                    'aggregate_root_id' => $this->uuidEncoder->encodeString($payload['headers'][Header::AGGREGATE_ROOT_ID]),
                    'version' => $payload['headers'][Header::AGGREGATE_ROOT_VERSION] ?? 0,
                    'payload' => json_encode($payload, $this->jsonEncodeOptions),
                ] + $this->tableSchema->additionalColumns();

            $documents[] = $document;
        }


        try {
            $this->getCollection()->insertMany($documents, ['writeConcern' => new WriteConcern('majority')]);
        } catch (Throwable $exception) {
            throw UnableToPersistMessages::dueTo('', $exception);
        }
    }


    public function retrieveAll(AggregateRootId $id): Generator
    {


        $options = [
            'sort' => ['version' => self::SORT_ASCENDING],
        ];

        $cursor = $this->getCollection()->find(['aggregate_root_id' => $id->toString()], $options);

        try {
            return $this->yieldMessagesFromPayloads($cursor);
        } catch (Throwable $exception) {
            throw UnableToRetrieveMessages::dueTo('', $exception);
        }
    }

    /**
     * @psalm-return Generator<Message>
     */
    public function retrieveAllAfterVersion(AggregateRootId $id, int $aggregateRootVersion): Generator
    {
        $options = [
            'sort' => ['version' => self::SORT_ASCENDING],
        ];

        $cursor = $this->getCollection()->find(['aggregate_root_id' => $id->toString(), 'version' => $aggregateRootVersion], $options);

        try {
            return $this->yieldMessagesFromPayloads($cursor);
        } catch (Throwable $exception) {
            throw UnableToRetrieveMessages::dueTo('', $exception);
        }
    }

    /**
     * @psalm-return Generator<Message>
     */
    private function yieldMessagesFromPayloads(iterable $payloads): Generator
    {
        foreach ($payloads as $payload) {
            yield $message = $this->serializer->unserializePayload(json_decode($payload, true));
        }

        return isset($message)
            ? $message->header(Header::AGGREGATE_ROOT_VERSION) ?: 0
            : 0;
    }

    public function paginate(PaginationCursor $cursor): Generator
    {
        if (!$cursor instanceof OffsetCursor) {
            throw new LogicException(sprintf('Wrong cursor type used, expected %s, received %s', OffsetCursor::class, get_class($cursor)));
        }

        $options = [
            'sort' => [
                'created_at' => self::SORT_ASCENDING,
                'version' => self::SORT_ASCENDING
            ],
            'skip' => $cursor->offset(),
            'limit' => $cursor->limit(),
            'projection' => ['payload']
        ];

        $resultCursor = $this->getCollection()->find([], $options);
        $numberOfMessages = 0;
        try {
            foreach ($resultCursor as $payload) {
                $numberOfMessages++;
                yield $this->serializer->unserializePayload(json_decode($payload, true));
            }
        } catch (Throwable $exception) {
            throw UnableToRetrieveMessages::dueTo($exception->getMessage(), $exception);
        }

        return $cursor->plusOffset($numberOfMessages);
    }
}
