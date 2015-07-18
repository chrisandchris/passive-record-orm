<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name InsertType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class InsertType implements TypeInterface {

    /** @var string the table to insert */
    private $table;
    /** @var string the mode to use (e.g. "ignore") */
    private $mode;


    function __construct($table, $mode = null) {
        $this->table = $table;
        $this->mode = $mode;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'insert';
    }

    /**
     * @return mixed
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getMode() {
        return $this->mode;
    }
}
