<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Logger;

use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement;

/**
 * @name PdoLogger
 * @version    1.0.0
 * @since      v1.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris/symfony-rowmapper
 * @deprecated v2.0.1 will be removed by 2.1.0
 */
class PdoLogger extends PdoLayer implements LoggerInterface {

    /** @var string this postfix could be added to a query string to disable logging for this string (used by log statement itself) */
    const DNL_POSTFIX = '-- @@DNL';
    /** @var bool whether the logging is enabled or not */
    private $logEnabled = true;
    /** @var bool whether the table was set up or not */
    private $hasLogTable;
    /** @var string a unique request id */
    private $requestId;

    /**
     * @inheritdoc
     */
    function disableLog() {
        $this->logEnabled = false;
    }

    /**
     * @inheritdoc
     */
    function enableLog() {
        $this->logEnabled = true;
    }

    /**
     * @inheritdoc
     */
    function writeToLog(PdoStatement $statement, $userId = null, $runtime = 0) {
        if (!$this->hasLogTable) {
            $this->createLogTable();
        }
        if (!$this->isLogEnabled()) {
            return;
        }
        try {
            $now = (new \DateTime())->format('Y-m-d H:i');
            $logStatement = $this->prepare(
                "INSERT INTO log
              (user_id, log_requestid, log_type, log_date, log_querymeta, log_exectime)
              VALUES (:uid, :requestId, :type, '".$now."', :meta, :exectime) ".
                self::DNL_POSTFIX
            );

            $meta = $this->parseMeta($statement->getMeta());
            if ($this->isNotLoggableQuery($statement->getMeta()['query'])) {
                return;
            }
            // on error, append error information
            if (!empty($statement->errorInfo())) {
                $meta .= "\n".serialize($statement->errorInfo());
            }

            $logStatement->bindValue('uid', $userId, \PDO::PARAM_INT);
            $logStatement->bindValue('requestId', $this->getRequestId());
            $logStatement->bindValue('type', $this->getQueryType($meta));
            $logStatement->bindValue('meta', $meta);
            $logStatement->bindValue('exectime', $runtime);

            $logStatement->execute();
        } catch (\Exception $e) {
            // ignore
        }
    }

    private function createLogTable() {
        $this->prepare(
            'CREATE TABLE IF NOT EXISTS log (
          log_id INTEGER PRIMARY KEY AUTO_INCREMENT ,
          user_id VARCHAR(255),
          log_requestid VARCHAR(255),
          log_type VARCHAR(255),
          log_date DATETIME,
          log_querymeta TEXT,
          log_exectime FLOAT
        ) ;'
        )
             ->execute();
        $this->hasLogTable = true;
    }

    function isLogEnabled() {
        return $this->logEnabled;
    }

    private function parseMeta(array $meta) {
        $neededMeta = $meta['query'];
        $neededMeta .= "\nValues:\n";
        if (!isset($meta['params'])) {
            return $neededMeta;
        }
        foreach ($meta['params'] as $parameter => $value) {
            $neededMeta .= "\t".$parameter.': '.$value."\n";
        }

        return $neededMeta;
    }

    /**
     * Validate whether we must log this query or not
     *
     * @param $meta
     * @return bool true if do not log this statement, false if log this
     *              statement
     */
    private function isNotLoggableQuery($meta) {
        if (mb_strstr($meta, self::DNL_POSTFIX) !== false) {
            return true;
        }

        return false;
    }

    private function getRequestId() {
        if (empty($this->requestId)) {
            $this->requestId = uniqid();
        }

        return $this->requestId;
    }

    /**
     * Get the query type
     *
     * @param $meta
     * @return null|string the query type (or null)
     */
    private function getQueryType($meta) {
        $lines = explode("\n", $meta, 2);
        $metaLine = mb_strtolower($lines[0]);
        $metaLine = explode(' ', $metaLine, 2);
        if (is_array($metaLine)) {
            return $metaLine[0];
        }

        return null;
    }
}
