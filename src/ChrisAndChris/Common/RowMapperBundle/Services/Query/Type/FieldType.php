<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name FieldType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class FieldType implements TypeInterface {

    private $identifier;

    function __construct($identifier) {
        $this->identifier = $identifier;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'field';
    }

    /**
     * @return mixed
     */
    public function getIdentifier() {
        return $this->identifier;
    }
}
