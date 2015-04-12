<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\TypeInterface;

/**
 * @name AbstractType
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
abstract class AbstractSnippet implements SnippetInterface {
    protected $type;

    /**
     * Set the type interface
     *
     * @param TypeInterface $type
     */
    function setType(TypeInterface $type) {
        $this->type = $type;
    }

    /**
     * @return TypeInterface
     */
    protected function getType() {
        return $this->type;
    }


}
