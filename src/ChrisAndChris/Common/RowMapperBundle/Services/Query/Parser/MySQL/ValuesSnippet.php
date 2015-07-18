<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name ValuesSnippet
 * @version    1.0.0
 * @since      v2.0.0
 * @package    KlitCommon
 * @subpackage RowMapperBundle
 * @author ChrisAndChris
 * @link   https://github.com/chrisandchris/symfony-rowmapper
 */
class ValuesSnippet extends AbstractSnippet {

    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'VALUES';
    }
}
