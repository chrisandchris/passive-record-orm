<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model\Mapping\Mapper;

use ChrisAndChris\Common\RowMapperBundle\Events\Transmitters\MapperEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Interface for database mapper
 *
 * @name MapperInterface
 * @version    1.0.0
 * @since      v2.2.0
 * @lastChange v2.2.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
interface MapperInterface extends EventSubscriberInterface
{

    public function getFields($schema, $table);

    public function getTables($schema);

    public function getRelations($schema, $table);

    /**
     * Adds this bag to bag event
     *
     * @param MapperEvent $event
     */
    public function onCollectorEvent(MapperEvent $event);
}
