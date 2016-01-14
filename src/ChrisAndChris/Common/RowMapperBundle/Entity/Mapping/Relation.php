<?php
namespace ChrisAndChris\Common\RowMapperBundle\Entity\Mapping;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;

/**
 * @name Relation
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class Relation implements Entity
{

    /** @var string */
    public $source;
    /** @var string */
    public $target;
    /** @var string */
    public $sourceField;
    /** @var string */
    public $targetField;
    /** @var string */
    public $alias;

    /**
     * @param string $source      the source table
     * @param string $target      the target table (joined table)
     * @param string $sourceField the source column name
     * @param string $targetField the target column name
     * @param string $alias       the table alias
     */
    public function __construct($source = null, $target = null, $sourceField = null, $targetField = null, $alias = null)
    {
        $this->target = $target;
        $this->targetField = $targetField;
        $this->source = $source;
        $this->sourceField = $sourceField;
        $this->alias = $alias;
    }
}
