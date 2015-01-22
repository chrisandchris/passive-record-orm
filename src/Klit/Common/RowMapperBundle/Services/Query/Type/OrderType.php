<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name OrderType
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class OrderType implements TypeInterface {
    protected $order;

    function __construct($order = null) {
        $this->order = $order;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'order';
    }

    /**
     * Get an array of instances of interfaces/classes allowed to get called after this type
     * Instances will be validated by $value instanceof $assigned
     *
     * @return array
     */
    function getAllowedChildren() {
        return array(

        );
    }

    function call($data) {
        if (!is_array($data)) {
            throw new \Exception("Illegal order");
        }
        $this->order = $data;
    }

    /**
     * @return mixed
     */
    public function getOrder() {
        return $this->order;
    }
}
