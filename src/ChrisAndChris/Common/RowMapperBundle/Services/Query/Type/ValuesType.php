<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

use Symfony\Component\Form\AbstractType;

/**
 * @name ValuesType
 * @version 1.0.0
 * @since v2.0.0
 * @package KlitCommon
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class ValuesType extends AbstractType {
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName() {
        return 'values';
    }
}
