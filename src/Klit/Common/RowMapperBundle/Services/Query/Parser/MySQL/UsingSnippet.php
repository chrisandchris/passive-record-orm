<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name UsingSnippet
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class UsingSnippet extends AbstractSnippet {
    /**
     * @inheritdoc
     */
    function getCode() {
        return 'USING(`' . $this->getType()->getField() . '`)';
    }
}
