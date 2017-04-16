<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Services\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Events\RowMapperEvents;
use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\MapperEvent;
use ChrisAndChris\Common\RowMapperBundle\Services\Utility\CacheWriter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @name DatabaseMapperCommand
 * @version    1.0.0
 * @since      v3.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class DatabaseMapperService
{

    /**
     * @var CacheWriter
     */
    private $cacheWriter;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var string
     */
    private $databaseDriver;
    /**
     * @var string
     */
    private $databaseSchema;


    /**
     * DatabaseMapperService constructor.
     *
     * @param CacheWriter              $cacheWriter
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $databaseDriver
     * @param string                   $databaseSchema
     */
    public function __construct(
        CacheWriter $cacheWriter,
        EventDispatcherInterface $eventDispatcher,
        string $databaseDriver,
        string $databaseSchema
    ) {
        $this->cacheWriter = $cacheWriter;
        $this->eventDispatcher = $eventDispatcher;
        $this->databaseDriver = $databaseDriver;
        $this->databaseSchema = $databaseSchema;
    }

    public function map()
    {
        $event = $this->eventDispatcher
            ->dispatch(
                RowMapperEvents::MAPPER_COLLECTOR,
                new MapperEvent()
            );

        $subsystem = $this->getSubsystem(
            $this->databaseDriver
        );

        $mapper = $event->getMapper($subsystem);

        $schema = $this->databaseSchema;

        $tables = $mapper->getTables($schema);
        $fields = [];
        $relations = [];
        foreach ($tables as $table) {
            $fields[$table] = $mapper->getFields($schema, $table);
            $relations[$table] = $mapper->getRelations($schema, $table);
        }

        $result = $this->merge($tables, $fields, $relations);
        $this->writeCache($result);
    }

    private function getSubsystem($subsystem)
    {
        $tests = [
            'mysql',
            'pgsql',
            'sqlite',
        ];
        foreach ($tests as $test) {
            if (strstr($subsystem, $test) !== false) {
                return $test;
            }
        }

        return false;
    }

    private function merge(array $tables, array $fields, array $relations)
    {
        $result = [];
        foreach ($tables as $table) {
            $result[$table] = [
                'fields'    => $fields[$table],
                'relations' => $relations[$table],
            ];
        }

        return $result;
    }

    private function writeCache(array $result) : bool
    {
        return $this->cacheWriter->writeToCache('mapping.json', json_encode($result));
    }
}
