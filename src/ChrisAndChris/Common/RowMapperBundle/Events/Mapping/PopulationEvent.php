<?php

namespace ChrisAndChris\Common\RowMapperBundle\Events\Mapping;

use ChrisAndChris\Common\RowMapperBundle\Entity\PopulateEntity;
use Symfony\Component\EventDispatcher\Event;

/**
 * @name PopulationEvent
 * @version    1.0.0
 * @since      v2.1.1
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class PopulationEvent extends Event
{

    /**
     * @var PopulateEntity
     */
    private $entity;
    /**
     * @var \Closure
     */
    private $entityFiller;
    /**
     * @var int
     */
    private $fieldCount;

    /**
     * PopulationEvent constructor.
     *
     * @param PopulateEntity $entity
     * @param \Closure       $entityFiller
     */
    public function __construct(PopulateEntity &$entity, \Closure $entityFiller)
    {
        $this->entity = $entity;
        $this->entityFiller = $entityFiller;
    }

    /**
     * @return PopulateEntity returns a clone of the entity
     */
    public function getEntity() : PopulateEntity
    {
        return clone $this->entity;
    }

    public function fill($field, $value, $mappingInfo = [])
    {
        $function = $this->entityFiller;
        $this->fieldCount += $function(
            $this->entity,
            $field,
            $value,
            $mappingInfo,
            true
        );
    }

    public function getWrittenFieldCount()
    {
        return $this->fieldCount;
    }
}
