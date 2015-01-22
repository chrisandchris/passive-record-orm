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
class FieldType implements TypeInterface {
    private $field;

    function __construct($field = null) {
        $this->field = $field;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'field';
    }

    /**
     * Get an array of instances of interfaces/classes allowed to get called after this type
     * Instances will be validated by $value instanceof $assigned
     *
     * @return array
     */
    function getAllowedChildren() {
        return array(
            new EqualsType(),
            new CloseType(),
            new OrType(),
            new AndType(),
            new BraceType()
        );
    }

    /**
     * Generic call method
     *
     * @param mixed $data
     */
    function call($data) {
        $this->field = $data;
    }

    /**
     * @return null
     */
    public function getField() {
        return $this->field;
    }
}
