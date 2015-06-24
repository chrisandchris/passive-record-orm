<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name ComparisonType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
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
