<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name DeleteType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class DeleteType implements TypeInterface {

    /** @var string */
    private $table;

    function __construct($table) {
        $this->table = $table;
    }

    /**
     * Get the name of the type
     *
     * @return string
     */
    function getTypeName() {
        return 'delete';
    }

    public function getTable() {
        return $this->table;
    }
}
