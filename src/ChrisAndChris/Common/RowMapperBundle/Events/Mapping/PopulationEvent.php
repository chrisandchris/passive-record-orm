<?php
namespace ChrisAndChris\Common\RowMapperBundle\Events\Mapping;

use ChrisAndChris\Common\RowMapperBundle\Entity\PopulateEntity;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Mapping\MissingContextException;
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
    /** @var [] */
    private $context;

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
    public function getEntity()
    {
        return clone $this->entity;
    }

    /**
     * @param $field
     * @param $value
     */
    public function fill($field, $value)
    {
        $function = $this->entityFiller;
        $this->fieldCount += $function($this->entity, $field, $value);
    }

    /**
     * @return int
     */
    public function getWrittenFieldCount()
    {
        return $this->fieldCount;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addContext($key, $value)
    {
        $this->context[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     * @throws MissingContextException
     */
    public function getContext($key)
    {
        if (isset($this->context[$key])) {
            return $this->context[$key];
        }

        throw new MissingContextException(sprintf(
            'Required context value for %s, but no such key ever set',
            $key
        ));
    }
}
