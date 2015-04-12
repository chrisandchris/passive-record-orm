<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name SelectType
 * @version 1.0.0
 * @since v2.0.0
 * @package KlitCommon
 * @subpackage RowMapper
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
}
