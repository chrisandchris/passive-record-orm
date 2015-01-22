<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use Klit\Common\RowMapperBundle\Services\Query\Type\FieldlistType;

/**
 * @name FieldSnippet
 * @version 1.0.0-dev
 * @package CommonRowMapper
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
        return '(#getFields)';
    }

    public function getFields() {
        $sql = '';
        $fieldCount = count($this->type->getFields());
        foreach ($this->type->getFields() as $idx => $value) {
            $sql .= '`' . $value . '`';
            if ($idx + 1 < $fieldCount) {
                $sql .= ', ';
            }
        }
        return $sql;
    }
}
