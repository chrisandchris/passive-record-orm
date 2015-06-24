<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name IsNullType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class IsNullType implements TypeInterface {

    private $isNull;

    function __construct($isNull = true) {
        $this->isNull = $isNull;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'isNull';
    }

    /**
     * @return mixed
     */
    public function isNull() {
        return $this->isNull;
    }
}
