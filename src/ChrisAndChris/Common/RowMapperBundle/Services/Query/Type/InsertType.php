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

    private $table;


    function __construct($table) {
        $this->table = $table;
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
}
