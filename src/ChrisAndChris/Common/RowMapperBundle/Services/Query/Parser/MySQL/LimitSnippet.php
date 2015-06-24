<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\LimitType;

/**
 * @name LimitSnippet
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class LimitSnippet extends AbstractSnippet {

    /** @var LimitType */
    protected $type;

    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'LIMIT #getLimit';
    }

    public function getLimit() {
        return abs($this->type->getLimit());
    }
}
