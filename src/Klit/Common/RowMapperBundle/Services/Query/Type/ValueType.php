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
}
