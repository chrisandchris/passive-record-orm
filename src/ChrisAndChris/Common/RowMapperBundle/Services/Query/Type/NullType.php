<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name NullType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class NullType implements TypeInterface {

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'null';
    }
}
