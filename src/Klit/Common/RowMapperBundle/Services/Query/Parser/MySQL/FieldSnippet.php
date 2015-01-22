<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\AbstractSnippet;
use Klit\Common\RowMapperBundle\Services\Query\Type\FieldType;

/**
 * @name FieldSnippet
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class FieldSnippet extends AbstractSnippet {
    /** @var FieldType */
    protected $type;
    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        return '`#getName`';
    }

    public function getName() {
        return $this->type->getField();
    }
}
