<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\FieldlistType;

/**
 * @name FieldSnippet
 * @version 1.0.0
 * @since v2.0.0
 * @package KlitCommon
 * @subpackage RowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class FieldlistSnippet extends AbstractSnippet {
    /** @var FieldlistType */
    protected $type;

    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return '#getFields';
    }

    public function getFields() {
        $sql = '';
        $fieldCount = count($this->type->getFields());
        $idx = 0;
        foreach ($this->type->getFields() as $key => $value) {
            if (strstr($key, ':') !== false) {
                $key = explode(':', $key);
                $key = '`' . implode('`.`', $key) . '`';
            }
            if (!is_numeric($key)) {
                $sql .= '`' . $key .'` as `' . $value . '`';
            } else {
                $sql .= '`' . $value . '`';
            }
            if (++$idx < $fieldCount) {
                $sql .= ', ';
            }
        }
        return $sql;
    }
}
