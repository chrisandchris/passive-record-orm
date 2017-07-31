<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Entity\Relation;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;

/**
 *
 *
 * @name ResultSet
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class ResultSet implements \Iterator, \Countable
{

    /** @var int */
    public $position;
    /** @var \ChrisAndChris\Common\RowMapperBundle\Entity\Relation\RelatedEntity[] */
    public $data;

    public function __construct(array $records = [])
    {
        foreach ($records as $record) {
            $this->push($record);
        }
        $this->position = 0;
    }

    public function push(RelatedEntity $entity)
    {
        $this->data[] = $entity;
    }

    function rewind()
    {
        $this->position = 0;
    }

    function key()
    {
        return $this->position;
    }

    function next()
    {
        ++$this->position;
    }

    function valid()
    {
        return isset($this->array[$this->position]);
    }

    public function only()
    {
        if ($this->count() == 1) {
            return $this->current();
        }

        throw new InvalidOptionException(sprintf(
            'Unable to get only record if set has more than 1 position'
        ));
    }

    public function count()
    {
        return count($this->data);
    }

    function current()
    {
        return $this->data[$this->position];
    }
}
