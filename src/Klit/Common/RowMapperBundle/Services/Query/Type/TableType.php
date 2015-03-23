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
        $this->table = $table;
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
}
