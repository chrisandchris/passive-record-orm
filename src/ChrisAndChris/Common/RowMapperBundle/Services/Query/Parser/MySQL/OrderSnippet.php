<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\OrderType;

/**
 * @name OrderSnippet
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class OrderSnippet extends AbstractSnippet {

    /** @var OrderType */
    protected $type;

    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'ORDER BY /@brace(order)';
    }
}
