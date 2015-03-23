<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name ParameterizedTypeInterface
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
interface ParameterizedTypeInterface extends TypeInterface {
    function getParameter($index);
}
