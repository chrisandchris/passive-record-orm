<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Type;
/**
 * @name UpdateType
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class UpdateType implements TypeInterface {
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
        return 'update';
    }

    /**
     * @return string
     */
    public function getTable() {
        return $this->table;
    }
}
