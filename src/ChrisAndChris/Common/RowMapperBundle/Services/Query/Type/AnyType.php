<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name AnyType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class AnyType implements TypeInterface {

    /**
     * @inheritdoc
     */
    public function getTypeName() {
        return 'any';
    }
}
