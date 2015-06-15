<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name ValuesType
 * @version    1.0.0
 * @since      v2.0.0
 * @package    KlitCommon
 * @subpackage RowMapperBundle
 * @author     Christian Klauenbösch <christian@klit.ch>
 * @copyright  Klauenbösch IT Services
 * @link       http://www.klit.ch
 */
class ValuesType implements TypeInterface {

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getTypeName() {
        return 'values';
    }
}