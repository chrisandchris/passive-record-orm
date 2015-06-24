<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name FunctionType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class FunctionType implements TypeInterface {

    /** @var string the function name */
    private $name;

    function __construct($name) {
        $this->name = $name;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'function';
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}
