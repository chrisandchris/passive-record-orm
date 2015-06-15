<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name WhereType
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class WhereType implements TypeInterface {
    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'where';
    }
}