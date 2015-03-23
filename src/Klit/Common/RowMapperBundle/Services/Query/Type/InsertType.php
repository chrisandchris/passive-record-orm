<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Type;

/**
 * @name InsertType
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class InsertType implements TypeInterface {
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
        return 'insert';
    }

    /**
     * @return mixed
     */
    public function getTable() {
        return $this->table;
    }
}
