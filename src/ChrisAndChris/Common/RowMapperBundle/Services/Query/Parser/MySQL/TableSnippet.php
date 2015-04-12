<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\TableType;

/**
 * @name TableSnippet
 * @version 1.0.0
 * @since v2.0.0
 * @package Common
 * @subpackage RowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class TableSnippet extends AbstractSnippet {
    /** @var TableType */
    protected $type;

    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'FROM `#getTable` #getAlias';
    }

    public function getTable() {
        $table = $this->type->getTable();
        if (is_array($table)) {
            return implode('`.`', $table);
        }
        return $this->type->getTable();
    }

    public function getAlias() {
        if ($this->type->getAlias() != null) {
            return 'as `' . $this->type->getAlias() . '`';
        }
        return null;
    }
}
