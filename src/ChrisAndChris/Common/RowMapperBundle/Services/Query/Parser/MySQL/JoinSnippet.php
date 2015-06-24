<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\JoinType;

/**
 * @name JoinSnippet
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class JoinSnippet extends AbstractSnippet {

    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        /** @var $Type JoinType */
        $Type = $this->getType();

        return strtoupper($Type->getJoinType()) . ' JOIN `' . $Type->getTable() . '`';
    }
}
