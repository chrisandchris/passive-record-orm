<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use Klit\Common\RowMapperBundle\Services\Query\Type\OrderType;

/**
 * @name OrderSnippet
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
