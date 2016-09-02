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
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
interface MapperInterface extends EventSubscriberInterface
{

    /**
     * @param $schema
     * @param $table
     * @return array
     */
    public function getFields($schema, $table);

    /**
     * @param $schema
     * @return array
     */
    public function getTables($schema);

    /**
     * @param $schema
     * @param $table
     * @return array
     */
    public function getRelations($schema, $table);

    /**
     * Adds this bag to bag event
     *
     * @param MapperEvent $event
     */
    public function onCollectorEvent(MapperEvent $event);
}
