<?php

namespace ChrisAndChris\Common\RowMapperBundle\Entity\Search;

/**
 * @name FilterCondition
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 * @deprecated Do not use FilterCondition, no replacement, tbr 3.1
 */
class FilterCondition
{

    /** @var string */
    public $table;
    /** @var string */
    public $field;
    /** @var string */
    public $requestValue;

    /**
     * @param string $table
     * @param string $field
     * @param string $requestedValue
     */
    public function __construct($table, $field, $requestedValue)
    {
        $this->table = $table;
        $this->field = $field;
        $this->requestValue = $requestedValue;
    }
}
