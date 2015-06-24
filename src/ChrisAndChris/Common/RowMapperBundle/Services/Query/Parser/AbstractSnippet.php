<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser;

use ChrisAndChris\Common\RowMapperBundle\Services\Query\Type\TypeInterface;

/**
 * @name AbstractType
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
abstract class AbstractSnippet implements SnippetInterface {

    protected $type;

    /**
     * @return TypeInterface
     */
    protected function getType() {
        return $this->type;
    }

    /**
     * Set the type interface
     *
     * @param TypeInterface $type
     */
    function setType(TypeInterface $type) {
        $this->type = $type;
    }
}
