<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name JoinType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class JoinType implements TypeInterface {

    private $table;
    private $joinType;

    function __construct($table, $joinType = 'inner') {
        $this->table = $table;
        $this->joinType = $joinType;
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'join';
    }

    /**
     * @return mixed
     */
    public function getTable() {
        return $this->table;
    }

    public function getJoinType() {
        return $this->joinType;
    }
}
