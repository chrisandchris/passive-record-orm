<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name UpdateSnippet
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class UpdateSnippet extends AbstractSnippet {

    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'UPDATE `#getTable` SET';
    }

    public function getTable() {
        return $this->getType()
                    ->getTable();
    }
}
