<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\LimitType;

/**
 * @name LimitSnippet
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
