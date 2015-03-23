<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name FieldType
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class FieldlistType implements TypeInterface {
    private $fields;

    function __construct($fields = null) {
        $this->fields = $fields;
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'fieldlist';
    }

    /**
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }
}
