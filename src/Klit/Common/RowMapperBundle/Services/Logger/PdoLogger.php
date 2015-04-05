<?php
namespace Klit\Common\RowMapperBundle\Services\Logger;

use Klit\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoStatement;

/**
 * @name PdoLogger
 * @version 1.0.0
 * @since v1.1.0
 * @package Common
 * @subpackage RowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class PdoLogger extends PdoLayer implements LoggerInterface {
    /** @var bool whether the logging is enabled or not */
    private $logEnabled = true;
    /** @var bool whether the table was set up or not */
    private $hasLogTable;
    /** @var string this postfix could be added to a query string to disable logging for this string (used by log statement itself) */
    private static $DNLPOSTFIX = '-- @@DNL';
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

    function isLogEnabled() {
        return $this->logEnabled;
    }

    /**
     * @inheritdoc
     */
    function writeToLog(PdoStatement $Statement, $userId = null, $runtime = 0) {
        if (!$this->hasLogTable) {
            $this->createLogTable();
        }
        if (!$this->isLogEnabled()) {
            return ;
        }
        try {
            $now = (new \DateTime())->format('Y-m-d H:i');
            $LogStatement = $this->prepare("INSERT INTO log
              (user_id, log_requestid, log_type, log_date, log_querymeta, log_exectime)
              VALUES (:uid, :requestId, :type, '" . $now . "', :meta, :exectime) " . self::$DNLPOSTFIX);

            $meta = $this->parseMeta($Statement->getMeta());
            if ($this->isNotLoggableQuery($Statement->getMeta()['query'])) {
                return ;
            }
            // on error, append error information
            if (!empty($Statement->errorInfo())) {
                $meta .= "\n" . serialize($Statement->errorInfo());
            }

            $LogStatement->bindValue('uid', $userId, \PDO::PARAM_INT);
            $LogStatement->bindValue('requestId', $this->getRequestId());
            $LogStatement->bindValue('type', $this->getQueryType($meta));
            $LogStatement->bindValue('meta', $meta);
            $LogStatement->bindValue('exectime', $runtime);

            $LogStatement->execute();
        } catch (\Exception $e) {
            // ignore
        }
    }

    private function createLogTable() {
        $this->prepare('CREATE TABLE IF NOT EXISTS log (
          log_id INTEGER PRIMARY KEY AUTO_INCREMENT ,
          user_id VARCHAR(255),
          log_requestid VARCHAR(255),
          log_type VARCHAR(255),
          log_date DATETIME,
          log_querymeta TEXT,
          log_exectime FLOAT
        ) ;')->execute();
        $this->hasLogTable = true;
    }

    /**
     * Validate whether we must log this query or not
     *
     * @param $meta
     * @return bool true if do not log this statement, false if log this statement
     */
    private function isNotLoggableQuery($meta) {
        if (mb_strstr($meta, self::$DNLPOSTFIX) !== false) {
            return true;
        }
        return false;
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
        if (mb_strstr($metaLine, 'insert')) {
            return 'INSERT';
        } else if (mb_strstr($metaLine, 'delete')) {
            return 'DELETE';
        } else if (mb_strstr($metaLine, 'update')) {
            return 'UPDATE';
        } else if (mb_strstr($metaLine, 'select')) {
            return 'SELECT';
        } else if (mb_strstr($metaLine, 'create')) {
            return 'create';
        } else if (mb_strstr($metaLine, 'alter')) {
            return 'alter';
        }
        return null;
    }

    private function parseMeta(array $meta) {
        $neededMeta = $meta['query'];
        $neededMeta .= "\nValues:\n";
        if (!isset($meta['params'])) {
            return $neededMeta;
        }
        foreach ($meta['params'] as $parameter => $value) {
            $neededMeta .= "\t" . $parameter . ': ' . $value . "\n";
        }
        return $neededMeta;
    }

    private function getRequestId() {
        if (empty($this->requestId)) {
            $this->requestId = uniqid();
        }
        return $this->requestId;
    }
}
