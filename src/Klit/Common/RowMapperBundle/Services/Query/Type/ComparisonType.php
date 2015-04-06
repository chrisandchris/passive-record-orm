<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name ComparisonType
 * @version 1.0.0
 * @since v2.0.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class ComparisonType implements TypeInterface {
    private $comparison;

    function __construct($comparison) {
        $this->comparison = $comparison;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'comparison';
    }

    /**
     * @return mixed
     */
    public function getComparison() {
        return $this->comparison;
    }
}
