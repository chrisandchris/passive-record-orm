<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name EqualsType
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class EqualsType implements TypeInterface {
    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'equals';
    }

    /**
     * Get an array of instances of interfaces/classes allowed to get called after this type
     * Instances will be validated by $value instanceof $assigned
     *
     * @return array
     */
    function getAllowedChildren() {
        return array(
            new ValueType(),
            new FieldType()
        );
    }

    /**
     * Generic call method
     *
     * @param mixed $data
     */
    function call($data) {
        // TODO: Implement call() method.
    }
}
