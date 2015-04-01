<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name AsSnippet
 * @version 1.0.0
 * @since v2.0.0
 * @package KlitCommon
 * @subpackage RowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class AsSnippet extends AbstractSnippet {
    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'as `#getName`';
    }

    public function getName() {
        return $this->getType()->getFieldName();
    }
}
