<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name OffsetSnippet
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class OffsetSnippet extends AbstractSnippet {

    /**
     * @inheritdoc
     */
    function getCode() {
        return 'OFFSET ' . $this->getType()
                                ->getOffset();
    }
}
