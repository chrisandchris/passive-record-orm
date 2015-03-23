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
    private $key = 0;
    /**
     * Get the code
     *
     * @return string
     */
    function getCode() {
        if (is_array($this->type->getIdentifier())) {
            $code = '';
            $id = 'A';
            foreach ($this->type->getIdentifier() as $key => $name) {
                $code .= '`#getNextIdentifier' . $id++ . '`';
                if (count($this->type->getIdentifier()) > $key + 1) {
                    $code .= '.';
                }
            }
        } else {
            $code = '`#getNextIdentifier`';
        }
        return $code;
    }

    public function __call($name, $arg) {
        return $this->getNextIdentifier();
    }

    public function getNextIdentifier() {
        if (is_array($this->type->getIdentifier())) {
            return $this->type->getIdentifier()[$this->key++];
        }
        return $this->type->getIdentifier();
    }
}
