<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name DeleteType
 * @version 1.0.0
 * @since 1.1.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
