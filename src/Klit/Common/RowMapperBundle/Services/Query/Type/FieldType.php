<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name FieldType
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
