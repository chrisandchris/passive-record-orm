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
}