<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use Klit\Common\RowMapperBundle\Services\Query\Type\TableType;

/**
 * @name TableSnippet
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class TableSnippet extends AbstractSnippet {
    /** @var TableType */
    protected $type;

    /**
     * Get the code
     *
     * @return string
     */
    // @todo implement explicit name database.table
    // @todo implement alias
    function getCode() {
       return 'FROM `#getTable`';
    }

    public function getTable() {
        return $this->type->getTable();
    }
}
