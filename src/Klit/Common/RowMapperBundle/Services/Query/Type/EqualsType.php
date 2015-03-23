<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name EqualsType
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class EqualsType implements TypeInterface {
    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'equals';
    }
}
