<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model;

use Symfony\Component\EventDispatcher\Event;

/**
 * @name DatabaseEvent
 * @version    1.0.0
 * @since      v2.0.1
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class DatabaseEvent extends Event {

    /** @var string the query type (select, update, ...) */
    private $type;
    /** @var string the primary affected table (if available) */
    private $table;
    /** @var string the affected primary key (if available) */
    private $primaryKey;
    /** @var bool true if a listener marked the event as "called" */
    private $called;

    /**
     * DatabaseEvent constructor.
     *
     * @param string $type       the query type (select, update, ...)
     * @param string $table      the primary affected table (if available)
     * @param string $primaryKey the affected primary key (if available)
     */
    public function __construct($type, $table = null, $primaryKey = null) {

        $this->type = $type;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getPrimaryKey() {
        return $this->primaryKey;
    }

    /**
     * Returns true if a listener marked the event as "called"
     * @return bool
     */
    public function isCalled()
    {
        return $this->called;
    }

    /**
     * Mark the event als "called", which means a listener executed some action using the event data
     */
    public function setCalled()
    {
        $this->called = true;
    }
}
