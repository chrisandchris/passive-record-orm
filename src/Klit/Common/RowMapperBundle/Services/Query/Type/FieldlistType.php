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
        $this->call($fields);
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'fieldlist';
    }

    /**
     * @inheritdoc
     */
    function getAllowedChildren() {
        return array(
            new TableType()
        );
    }

    /**
     * Generic call method
     *
     * @param mixed $data
     */
    function call($data) {
        $this->fields = $data;
    }

    /**
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }
}
