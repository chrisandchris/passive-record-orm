<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name JoinType
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
     * Generic call method
     *
     * @param mixed $table
     */
    function call($table) {
        $this->table = $table;
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
