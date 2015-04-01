<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name AsType
 * @version 1.0.0
 * @since v2.0.0
 * @package KlitCommon
 * @subpackage RowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class AsType implements TypeInterface {
    /** @var string */
    private $fieldName;

    function __construct($fieldName) {
        $this->fieldName = $fieldName;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'as';
    }

    /**
     * @return string
     */
    public function getFieldName() {
        return $this->fieldName;
    }
}
