<?php
namespace ChrisAndChris\Common\RowMapperBundle\Entity\Mapping;

/**
 * @name Field
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class Field
{

    /** @var string */
    public $table;
    /** @var string */
    public $field;

    /**
     * @param string $table
     * @param string $field
     */
    public function __construct($table = null, $field = null)
    {
        $this->table = $table;
        $this->field = $field;
    }
}
