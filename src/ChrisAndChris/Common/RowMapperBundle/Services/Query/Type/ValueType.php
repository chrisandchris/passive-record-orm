<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name ValueType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
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
