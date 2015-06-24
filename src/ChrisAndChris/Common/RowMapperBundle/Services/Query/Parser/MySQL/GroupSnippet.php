<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name GroupSnippet
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class GroupSnippet extends AbstractSnippet {

    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'GROUP BY /@brace(group)';
    }
}
