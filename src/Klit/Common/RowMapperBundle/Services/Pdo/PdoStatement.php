<?php
namespace Klit\Cipromat\DatabaseBundle\Services\Pdo;

use Klit\Common\RowMapperBundle\Services\Pdo\PdoLogger;
use PDO;

/**
 * Extends the official pdo statement to provide statement logging
 *
 * @name PdoStatement
 * @version 1.1.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
class PdoStatement extends \PDOStatement {
    /** @var string a unique request id */
    private static $requestId;
    /** @var string|int the user id (or username) */
    private static $userId;
    /** @var PdoLogger the pdo connection to log statements */
    private $PdoLogger;
    /** @var string this postfix could be added to a query string to disable logging for this string (used by log statement itself) */
    private static $DNLPOSTFIX = '-- @@DNL';
    /** @var array the list of set parameters */
    private $params = array();
    /** @var float the time needed to execute the query */
    private $executionTime;

    protected function __construct(PdoLogger $PdoLayer = null) {
        $this->PdoLogger = $PdoLayer;
    }

    /**
     * Set the actual user
     *
     * @param int|string $userId the user id of the user that executes this request
     */
    public static function setUser($userId) {
        if (self::$userId == null) {
            self::$userId = $userId;
        }
    }

    /**
     * Gets the actual request id
     * @return string the request id
     */
    private static function getRequestId() {
        if (self::$requestId == null) {
            self::$requestId = uniqid();
        }
        return self::$requestId;
    }

    public function execute($input_parameters = null) {
        // write log first, even if query fails (else any access to \PDO::lastInsertId() has wrong results!
        $start = microtime(true);
        $result = parent::execute($input_parameters);
        $this->executionTime = microtime(true) - $start;
        $this->writeLog();
        return $result;
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR) {
        $this->params[$parameter] = $value;
        return parent::bindValue($parameter, $value, $data_type);
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null) {
        $this->params[$parameter] = $variable;
        return parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null) {
        $this->params[$column] = $param;
        return parent::bindColumn($column, $param, $type, $maxlen, $driverdata);
    }

    /**
     * Get meta data of the query
     * @return string
     */
    private function getMeta() {
        $neededMeta = $this->queryString;
        $neededMeta .= "\nValues:\n";
        foreach ($this->params as $parameter => $value) {
            $neededMeta .= "\t" . $parameter . ': ' . $value . "\n";
        }
        return $neededMeta;
    }

    /**
     * Log the query
     */
    private function writeLog() {
        try {
            if ($this->PdoLogger === null) {
                return ;
            }
            $statement = $this->PdoLogger->prepare('INSERT INTO log
              (user_id, log_requestid, log_type, log_date, log_querymeta, log_exectime)
              VALUES (:uid, :requestId, :type, NOW(), :meta, :exectime) ' . self::$DNLPOSTFIX);

            $meta = $this->getMeta();
            if ($this->isNotLoggableQuery($meta)) {
                return ;
            }

            $statement->bindValue('uid', self::$userId, \PDO::PARAM_INT);
            $statement->bindValue('requestId', self::getRequestId());
            $statement->bindValue('type', $this->getQueryType($meta));
            $statement->bindValue('meta', $meta);
            $statement->bindValue('exectime', $this->executionTime);

            $statement->execute();
        } catch (\Exception $e) {
            // ignore
        }
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
        } else {
            if (mb_strstr($metaLine, 'delete')) {
                return 'DELETE';
            } else {
                if (mb_strstr($metaLine, 'update')) {
                    return 'UPDATE';
                } else {
                    if (mb_strstr($metaLine, 'select')) {
                        return 'SELECT';
                    }
                }
            }
        }
        return null;
    }

    /**
     * Validate whether we must log this query or not
     *
     * @param $meta
     * @return bool true if not loggable, false if loggable
     */
    private function isNotLoggableQuery($meta) {
        if (mb_strstr($meta, self::$DNLPOSTFIX) !== false) {
            return true;
        }
        return false;
    }
}
