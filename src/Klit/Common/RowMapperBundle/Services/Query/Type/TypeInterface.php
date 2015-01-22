<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name SelectType
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
interface TypeInterface {
    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName();

    /**
     * Get an array of instances of interfaces/classes allowed to get called after this type
     * Instances will be validated by $value instanceof $assigned
     *
     * @return array
     */
    function getAllowedChildren();

    /**
     * Generic call method
     *
     * @param mixed $data
     */
    function call($data);
}