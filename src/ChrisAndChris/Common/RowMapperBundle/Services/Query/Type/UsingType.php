<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name UsingType
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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