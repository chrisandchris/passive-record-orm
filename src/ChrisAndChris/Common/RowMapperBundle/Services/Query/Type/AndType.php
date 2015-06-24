<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name AndType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class AndType implements TypeInterface {

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'and';
    }
}
