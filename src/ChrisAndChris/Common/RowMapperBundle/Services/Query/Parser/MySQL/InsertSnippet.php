<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name InsertSnippet
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class InsertSnippet extends AbstractSnippet {

    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        if ($this->getType()
                 ->getMode() == 'ignore'
        ) {
            return 'INSERT IGNORE INTO `'.$this->getType()
                                               ->getTable().'`';
        } else {
            if ($this->getType()
                     ->getMode() == 'delayed'
            ) {
                return 'INSERT DELAYED INTO `'.$this->getType()
                                                    ->getTable().'`';
            }
        }

        return 'INSERT INTO `'.$this->getType()
                                    ->getTable().'`';
    }
}
