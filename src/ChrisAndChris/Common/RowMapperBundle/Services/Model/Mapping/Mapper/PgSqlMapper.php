<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Events\RowMapperEvents;
use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\MapperEvent;

/**
 * @name PgSqlMapper
 * @version    1.0.0
 * @since      v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class PgSqlMapper implements MapperInterface
{

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            RowMapperEvents::MAPPER_COLLECTOR => ['onCollectorEvent'],
        ];
    }

    public function getFields($schema, $table)
    {
        // TODO: Implement getFields() method.
    }

    public function getTables($schema)
    {
        // TODO: Implement getTables() method.
    }

    public function getRelations($schema, $table)
    {
        // TODO: Implement getRelations() method.
    }

    /**
     * @inheritDoc
     */
    public function onCollectorEvent(MapperEvent $event)
    {
        $event->add($this, 'pgsql');
    }
}
