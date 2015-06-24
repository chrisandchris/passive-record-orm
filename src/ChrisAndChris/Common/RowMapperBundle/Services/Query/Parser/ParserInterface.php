<?php

namespace ChrisAndChris\Common\RowMapperBundle\Services\Query\Parser;

/**
 * @name ParserInterface
 * @version   1.0.0
 * @since     v2.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
interface ParserInterface {

    /**
     * Set the query information
     *
     * @param array $statement
     */
    function setStatement(array $statement);

    /**
     * Parse the statement
     */
    function execute();

    /**
     * Get the parsed statement
     *
     * @return string
     */
    function getSqlQuery();

    /**
     * Get the parameters array
     *
     * @return array
     */
    function getParameters();
}
