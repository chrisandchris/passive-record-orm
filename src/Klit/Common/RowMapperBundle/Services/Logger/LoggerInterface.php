<?php
namespace Klit\Common\RowMapperBundle\Services\Logger;

use Klit\Common\RowMapperBundle\Services\Pdo\PdoStatement;

/**
 * @name LoggerInterface
 * @version 1.0.0
 * @since v1.1.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
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
     * @param PdoStatement $Statement
     * @param string $userId a unique id representing the executing user
     * @param int $runtime runtime information (time in ms as float)
     * @return null
     */
    function writeToLog(PdoStatement $Statement, $userId = null, $runtime = 0);
}
