<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name LikeSnippet
 * @version   1.0.0
 * @since     2.0.0
 * @package   RowMapperBundle
 * @author    Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link      http://www.klit.ch
 */
class LikeSnippet extends AbstractSnippet {

    /**
     * @inheritdoc
     */
    function getCode() {
        return 'LIKE ?';
    }
}
