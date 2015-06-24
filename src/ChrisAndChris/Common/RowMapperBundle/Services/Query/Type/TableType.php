<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name TableType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class TableType implements TypeInterface {

    private $table;
    private $alias;

    function __construct($table, $alias = null) {
        $this->table = $table;
        $this->alias = $alias;
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'table';
    }

    /**
     * @return mixed
     */
    public function getTable() {
        return $this->table;
    }

    public function getAlias() {
        return $this->alias;
    }
}
