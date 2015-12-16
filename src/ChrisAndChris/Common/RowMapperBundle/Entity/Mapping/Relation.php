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

    /**
     * @param string $target
     * @param string $targetField
     * @param string $source
     * @param string $sourceField
     */
    public function __construct($source = null, $target = null, $sourceField = null, $targetField = null)
    {
        $this->target = $target;
        $this->targetField = $targetField;
        $this->source = $source;
        $this->sourceField = $sourceField;
    }
}
