<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name CommaType
 * @version 
 * @since 
 * @package 
 * @subpackage 
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class CommaType implements TypeInterface {
    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'comma';
    }
}
