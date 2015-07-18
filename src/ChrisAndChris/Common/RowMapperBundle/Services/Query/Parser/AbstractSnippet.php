<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
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

    protected function implodeIdentifier($identifier) {
        // $identifier = 'database:table:field
        if (strstr($identifier, ':') !== false) {
            return '`'.implode('`.`', explode(':', $identifier)).'`';
        } else {
            if (is_array($identifier)) {
                return '`'.implode('`.`', $identifier).'`';
            } else {
                if (is_string($identifier)) {
                    return '`'.$identifier.'`';
                }
            }
        }

        throw new InvalidOptionException('Invalid input given');
    }
}
