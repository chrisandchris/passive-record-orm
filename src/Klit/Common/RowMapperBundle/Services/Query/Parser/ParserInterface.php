<?php

namespace Klit\Common\RowMapperBundle\Services\Query\Parser;

/**
 * @name ParserInterface
 * @version 1.0.0-dev
 * @package CommonRowMapper
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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