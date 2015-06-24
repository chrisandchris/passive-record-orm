<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\RawType;

/**
 * @name RawSnippet
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link      http://www.klit.ch
 */
class RawSnippet extends AbstractSnippet {

    /** @var RawType */
    protected $type;

    /**
     * @inheritdoc
     */
    function getCode() {
        return $this->getType()
                    ->getRaw();
    }
}
