<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name OrderBySnippet
 * @version
 * @package
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class OrderBySnippet extends AbstractSnippet {
    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return '`#getField` #getDirection';
    }

    public function getField() {
        return $this->getType()->getField();
    }

    public function getDirection() {
        return strtoupper($this->getType()->getDirection());
    }
}
