<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name ValueType
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class ValueType implements ParameterizedTypeInterface {
    private $value;

    function __construct($value = null) {
        $this->value = $value;
    }

    function getParameter($index) {
        return $this->value;
    }


    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'value';
    }

    /**
     * Get an array of instances of interfaces/classes allowed to get called after this type
     * Instances will be validated by $value instanceof $assigned
     *
     * @return array
     */
    function getAllowedChildren() {
        return array(
            new CloseType(),
            new AndType(),
            new OrType(),
            new BraceType()
        );
    }

    /**
     * Generic call method
     *
     * @param mixed $data
     */
    function call($data) {
        $this->value = $data;
    }
}
