<?php

namespace App\Infra\Traits;


use Alcaeus\MongoDbAdapter\TypeInterface;
use App\Infra\Database\Database;
use MongoDB\BSON;
use MongoDB\Client;
use MongoDB\Collection;

/**
 * Trait MongoDbTrait.
 *
 * @category PHP
 *
 * @author   hireme <info@hire-me.io>
 * @license  https://hireme.io/LICENSE.txt hireme Licence
 *
 * @link     https://hireme.io
 */
trait MongodbTrait
{
    public static $SORT = 'sort';
    public static $SKIP = 'skip';
    public static $LIMIT = 'limit';
    public static $BATCH_SIZE = 1000;
    public $selectedDatabase = Database::DATA_BASE_SELECTED;

    /**
     * @var Client
     */
    private $mongoClient;

    /**
     * @var Collection
     */
    private $mainCollection;

    /**
     * @param string $collection
     * @return Collection
     */
    public function getCollection(string $collection)
    {
        return $this->mongoClient->selectDatabase($this->selectedDatabase)->selectCollection($collection);
    }

    /**
     * @param array $criteria
     * @param array $newobj
     * @param array $options
     * @return \MongoDB\UpdateResult
     */
    public function update(array $criteria, array $newobj, array $options = [])
    {
        return $this->mainCollection->updateOne($criteria, $newobj, $options);
    }

    /**
     * @param array $criteria
     * @param array $newobj
     * @param array $options
     * @return \MongoDB\UpdateResult
     */
    public function updateMany(array $criteria, array $newobj, array $options = [])
    {
        return $this->mainCollection->updateMany($criteria, $newobj, $options);
    }

    /**
     * @param array $query
     * @param array $options
     * @return int
     */
    public function count(array $query = [], array $options = [])
    {
        return $this->mainCollection->countDocuments($query, $options);
    }

    /**
     * @param array $data
     * @param array $options
     * @return \MongoDB\InsertOneResult
     */
    public function insert(array $data, array $options = [])
    {
        return $this->mainCollection->insertOne($data, $options);
    }

    /**
     * @return mixed
     */
    public function findAll()
    {
        return self::toLegacy($this->mainCollection->find()->toArray());
    }

    /**
     * @param array $query
     * @param array $fields
     * @param array $context
     * @return array|\ArrayObject|int|BSON\Type
     */
    public function find(array $query = [], array $fields = [], array $context = [])
    {
        $options = [];
        if (!empty($fields)) {
            $options['projection'] = $fields;
        }

        if (isset($context[self::$SORT]) && 0 !== count($context[self::$SORT])) {
            $options['sort'] = $context[self::$SORT];
        }
        if (isset($context[self::$SKIP])) {
            $options['skip'] = (int)$context[self::$SKIP];
        }
        if (isset($context[self::$LIMIT])) {
            $options['limit'] = (int)$context[self::$LIMIT];
        }

        return self::toLegacy($this->mainCollection->find($query, $options)->toArray());
    }

    /**
     * @param string $id
     * @return array|\ArrayObject|int|BSON\Type|null
     */
    public function findById(string $id)
    {
        return self::toLegacy($this->findOne(['_id' => $id]));
    }

    /**
     * @param array $query
     * @param array $fields
     * @param array $options
     * @param bool $toLegacy
     * @return array|\ArrayObject|int|BSON\Type|null
     */
    public function findOne(array $query = [], array $fields = [], array $options = [], $toLegacy = true)
    {
        $queryOptions = [];
        if (!empty($fields)) {
            $queryOptions['projection'] = $fields;
        }

        if (isset($options[self::$SORT]) && 0 !== count($options[self::$SORT])) {
            $queryOptions['sort'] = $options[self::$SORT];
        }
        if (isset($options[self::$SKIP])) {
            $queryOptions['skip'] = (int)$options[self::$SKIP];
        }

        return self::toLegacy($this->mainCollection->findOne($query, $queryOptions));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findByIdWithoutConvertBSONObject(string $id)
    {
        return self::toLegacy($this->mainCollection->findOne(['_id' => $id]));
    }

    /**
     * @param array $query
     * @param array|null $update
     * @param array|null $fields
     * @param array $options
     * @return array|int|BSON\UTCDateTime|BSON\Timestamp
     */
    public function findAndModify(array $query, array $update = null, array $fields = null, array $options = [])
    {
        $queryOptions = [];
        if (!empty($fields)) {
            $queryOptions['projection'] = $fields;
        }

        if (isset($options[self::$SORT]) && 0 !== count($options[self::$SORT])) {
            $queryOptions['sort'] = $options[self::$SORT];
        }
        if (isset($options[self::$SKIP])) {
            $queryOptions['skip'] = (int)$options[self::$SKIP];
        }

        return self::toLegacy($this->mainCollection->findOneAndUpdate($query, $update, $queryOptions));
    }

    /**
     * @param mixed $value
     * @return array|int|BSON\Timestamp|BSON\UTCDateTime
     */
    public static function toLegacy($value)
    {
        switch (true) {
            case $value instanceof TypeInterface:
            case $value instanceof BSON\Type:
                return self::convertBSONObjectToLegacy($value);
            case is_array($value):
                $result = [];
                foreach ($value as $key => $item) {
                    $result[$key] = self::toLegacy($item);
                }

                return $result;
            case is_object($value):
                $result = [];
                foreach ($value as $key => $item) {
                    $result[] = self::toLegacy($item);
                }

                return $result;
            default:
                return $value;
        }
    }

    /**
     * @param mixed $value
     * @return int
     */
    private static function convertBSONObjectToLegacy($value)
    {
        switch (true) {
            case $value instanceof \MongoTimestamp:
            case $value instanceof BSON\Timestamp:
                return $value->sec;
            case $value instanceof \MongoDate:
            case $value instanceof BSON\UTCDateTime:
                return $value->toDateTime()->getTimestamp();
            default:
                return $value;
        }
    }

    /**
     * @param array $pipeline
     * @param array $op
     * @return array
     */
    public function aggregate(array $pipeline, array $op = [])
    {
        $t['result'] = [];
        $op['useCursor'] = true;
        $op['batchSize'] = self::$BATCH_SIZE;
        $t['result'] = self::toLegacy($this->mainCollection->aggregate($pipeline, $op)->toArray());

        return $t;
    }

    /**
     * @param array $criteria
     * @param array $options
     * @return \MongoDB\DeleteResult
     */
    public function remove(array $criteria = [], array $options = [])
    {
        return $this->mainCollection->deleteOne($criteria, $options);
    }

    /**
     * @param array $criteria
     * @param array $options
     * @return \MongoDB\DeleteResult
     */
    public function removeMany(array $criteria = [], array $options = [])
    {
        return $this->mainCollection->deleteMany($criteria, $options);
    }

    /**
     * @param string $fieldName
     * @param array $criteria
     * @param array $options
     * @return mixed[]
     */
    public function distinct(string $fieldName, array $criteria = [], array $options = [])
    {
        $result = $this->mainCollection->distinct($fieldName, $criteria, $options);
        if (isset($options[self::$LIMIT]) && is_int($options[self::$LIMIT]) && 0 != $options[self::$LIMIT]) {
            $result = array_slice($result, 0, $options[self::$LIMIT]);
        }

        return self::toLegacy($result);
    }
}