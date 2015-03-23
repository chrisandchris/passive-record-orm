<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name UpdateSnippet
 * @version 1.0.0
 * @since 1.1.0
 * @package Common
 * @subpackage RowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class UpdateSnippet extends AbstractSnippet{
    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'UPDATE `#getTable` SET';
    }

    public function getTable() {
        return $this->getType()->getTable();
    }
}
