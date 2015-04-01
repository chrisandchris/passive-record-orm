<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;

/**
 * @name ValuesSnippet
 * @version 1.0.0
 * @since v2.0.0
 * @package KlitCommon
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 * @todo multiple values statement
 */
class ValuesSnippet extends AbstractSnippet {
    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return 'VALUES @brace(values)';
    }
}
