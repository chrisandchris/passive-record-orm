<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name OrderByType
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
