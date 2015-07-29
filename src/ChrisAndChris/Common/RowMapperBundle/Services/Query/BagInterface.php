<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Query;

/**
 * @name BagInterface
 * @version   1.0.0
 * @since     v2.0.2
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
interface BagInterface {

    /**
     * Set an entry in the bag
     *
     * @param string $name  the name of the entry
     * @param mixed  $value the desired value
     * @return mixed
     */
    function set($name, $value);

    /**
     * Get an entry from the bag
     *
     * @param string $name the name of the entry
     * @return mixed
     */
    function get($name);

    /**
     * Get all entries from the bag
     *
     * @return array
     */
    function getAll();

    /**
     * Validates whether the given entry exists or not
     *
     * @param string $name the name of the entry
     * @return bool
     */
    function has($name);
}
