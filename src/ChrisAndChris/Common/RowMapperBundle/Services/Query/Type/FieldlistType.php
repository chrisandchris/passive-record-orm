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
class FieldlistType implements TypeInterface {

    private $fields;

    function __construct(array $fields = null) {
        $this->fields = $fields;
    }

    /**
     * @inheritdoc
     */
    function getTypeName() {
        return 'fieldlist';
    }

    /**
     * @return array
     */
    public function getFields() {
        return $this->fields;
    }
}
