<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name UsingSnippet
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class UsingSnippet extends AbstractSnippet {

    /**
     * @inheritdoc
     */
    function getCode() {
        return 'USING(`' . $this->getType()
                                ->getField() . '`)';
    }
}
