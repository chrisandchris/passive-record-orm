<?php
namespace Klit\Common\RowMapperBundle\Services\Query\Parser\MySQL;

use Klit\Common\RowMapperBundle\Services\Query\Parser\SnippetInterface;
use Klit\Common\RowMapperBundle\Services\Query\Type\SelectType;
use Klit\Common\RowMapperBundle\Services\Query\Type\TypeInterface;

/**
 * @name SelectSnippet
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class SelectSnippet implements SnippetInterface {
    /** @var SelectType */
    private $type;

    function setType(TypeInterface $type) {
        if ($type instanceof SelectType) {
            $this->type = $type;
            return ;
        }
        throw new \Exception("Invalid type given");
    }


    public function getCode() {
        return 'SELECT';
    }
}
