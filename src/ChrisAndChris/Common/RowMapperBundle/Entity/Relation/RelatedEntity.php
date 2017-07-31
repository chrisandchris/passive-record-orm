<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Entity\Relation;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;

/**
 *
 *
 * @name RelatedEntity
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
abstract class RelatedEntity implements RelatedEntityInterface
{

    /** @var ResultSet[] */
    private $relations;

    /**
     * @return \ChrisAndChris\Common\RowMapperBundle\Entity\Relation\Relation[]
     */
    abstract public function getRelations() : array;

    public function set(string $target, ResultSet $entity)
    {
        $this->relations[$target] = $entity;
    }

    public function get(string $target) : ResultSet
    {
        if (!isset($this->relations[$target])) {
            throw new InvalidOptionException(sprintf(
                'The target %s does not exist',
                $target
            ));
        }

        return $this->relations[$target];
    }
}
