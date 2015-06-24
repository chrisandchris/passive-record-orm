<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name UsingType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class UsingType implements TypeInterface {

    /** @var string */
    private $field;

    /**
     * Create instance
     *
     * @param string $field
     */
    function __construct($field) {
        $this->field = $field;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'using';
    }

    /**
     * @return string
     */
    public function getField() {
        return $this->field;
    }
}
