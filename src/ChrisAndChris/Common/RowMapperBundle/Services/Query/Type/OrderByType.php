<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name OrderByType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class OrderByType implements TypeInterface {

    private $direction;
    private $field;

    function __construct($field, $direction = 'desc') {
        $this->field = $field;
        $this->direction = $direction;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'orderBy';
    }

    /**
     * @return string
     */
    public function getDirection() {
        return $this->direction;
    }

    /**
     * @return mixed
     */
    public function getField() {
        return $this->field;
    }
}
