<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name TableType
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class TableType implements TypeInterface {
    private $table;

    function __construct($table = null) {
        $this->call($table);
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'table';
    }

    /**
     * @inheritdoc
     */
    function getAllowedChildren() {
        return array(
            new JoinType(),
            new WhereType(),
            new CloseType(),
            new LimitType()
        );
    }

    /**
     * Generic call method
     *
     * @param mixed $data
     */
    function call($data) {
        $this->table = $data;
    }

    /**
     * @return mixed
     */
    public function getTable() {
        return $this->table;
    }
}
