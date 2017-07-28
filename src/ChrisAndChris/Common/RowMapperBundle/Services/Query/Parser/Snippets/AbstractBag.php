<?php

namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser\Snippets;

use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;

/**
 *
 *
 * @name AbstractBag
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
abstract class AbstractBag
{

    /**
     * @param string $identifier the identifier to implode
     * @param string $d          the delimiter to use
     * @return string
     * @throws \ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException
     */
    public function implodeIdentifier($identifier, $d)
    {
        // $identifier = 'database:table:field'
        if (!is_array($identifier) && strstr($identifier, ':') !== false) {
            return $d . implode($d . '.' . $d, explode(':', $identifier)) . $d;
        } else {
            if (is_array($identifier)) {
                return $d . implode($d . '.' . $d, $identifier) . $d;
            } else {
                if (is_string($identifier)) {
                    return $d . $identifier . $d;
                }
            }
        }

        throw new InvalidOptionException(sprintf(
            'Invalid input given: %s',
            $identifier
        ));
    }

    public function toCamelCase($string)
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }
}
