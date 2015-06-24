<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name SelectType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class SelectType implements TypeInterface {

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'select';
    }
}
