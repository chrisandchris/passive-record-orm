<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name FunctionType
 * @version 1.0.0
 * @since v2.0.0
 * @package KlitCommon
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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