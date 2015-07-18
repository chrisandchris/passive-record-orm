<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Logger;

use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement;

/**
 * @name LoggerInterface
 * @version    1.0.0
 * @since      v1.1.0
 * @package    Common
 * @subpackage RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris/symfony-rowmapper
 */
interface LoggerInterface {

    /**
     * Disables logging for all further statements
     *
     * @return null
     */
    function disableLog();

    /**
     * Enables logging for all further statements
     *
     * @return null
     */
    function enableLog();

    /**
     * Returns whether the logging is enabled or not
     *
     * @return null
     */
    function isLogEnabled();

    /**
     * Appends a statement to the log
     *
     * @param PdoStatement $statement
     * @param string       $userId  a unique id representing the executing user
     * @param int          $runtime runtime information (time in ms as float)
     * @return null
     * @deprecated v2.0.1, will be removed by v2.1.0
     */
    function writeToLog(PdoStatement $statement, $userId = null, $runtime = 0);
}
