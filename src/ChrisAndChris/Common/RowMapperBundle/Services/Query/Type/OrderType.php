<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name OrderSnippet
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class OrderType implements TypeInterface {
    function __construct() {
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'order';
    }
}
