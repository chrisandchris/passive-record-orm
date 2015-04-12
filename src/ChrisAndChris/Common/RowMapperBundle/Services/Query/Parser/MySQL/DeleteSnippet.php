<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name DeleteSnippet
 * @version 
 * @since 
 * @package 
 * @subpackage 
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class DeleteSnippet extends AbstractSnippet {
    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'DELETE FROM `#getTable`';
    }

    public function getTable() {
        return $this->getType()->getTable();
    }
}
