<?php
namespace Klit\Common\RowMapperBundle\Services\Model;

use Klit\Common\RowMapperBundle\Entity\Entity;
use Klit\Common\RowMapperBundle\Exceptions\DatabaseException;
use Klit\Common\RowMapperBundle\Exceptions\ForeignKeyConstraintException;
use Klit\Common\RowMapperBundle\Exceptions\TransactionException;
use Klit\Common\RowMapperBundle\Exceptions\UniqueConstraintException;
use Klit\Common\RowMapperBundle\Services\Logger\LoggerInterface;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoStatement;
use Klit\Common\RowMapperBundle\Services\Pdo\RowMapper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @name Model
 * @version 2.0.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
abstract class Model {
    /** @var PdoLayer the pdo class */
    private $PDO;
    /** @var RowMapper the row mapper */
    private $Mapper;
    /** @var bool if set to true, current result must have at least one row */
    private $currentMustHaveRow;
    /** @var ErrorHandler */
    private $ErrorHandler;
    /** @var LoggerInterface the logger used to log statements */
    private $Logger;
    /** @var string a id representing the current user */
    private $userId;

    function __construct(PdoLayer $PDO, RowMapper $mapper, ErrorHandler $ErrorHandler, LoggerInterface $Logger) {
        $this->PDO = $PDO;
        $this->Mapper = $mapper;
        $this->ErrorHandler = $ErrorHandler;
        $this->Logger = $Logger;
    }

    public function setRunningUser($userId) {
        $this->userId = $userId;
    }

    /**
     * Set the PDO class
     *
     * @param \PDO $PDO
     * @deprecated
     */
    protected function setPdo(\PDO $PDO) {
        $this->PDO = $PDO;
    }

    /**
     * Set the Mapper
     *
     * @param \Klit\Common\RowMapperBundle\Services\Pdo\RowMapper $Mapper
     */
    protected function setMapper($Mapper) {
        $this->Mapper = $Mapper;
    }

    /**
     * Get the PDO class
     *
     * @return \PDO
     */
    protected  function getPDO() {
        return $this->PDO;
    }

    /**
     * Get the Mapper
     *
     * @return RowMapper
     */
    protected  function getMapper() {
        return $this->Mapper;
    }

    /**
     * Create a new statement from SQL-Code
     *
     * @param $sql
     * @return \PDOStatement
     */
    protected function createStatement($sql) {
        return $this->PDO->prepare($sql);
    }

    /**
     * Execute a PDOStatement and writes it to the log
     *
     * @param PdoStatement $statement
     * @return mixed
     */
    protected function execute(PdoStatement $statement) {
        $start = microtime(true);
        $result = $statement->execute();
        $time = microtime(true) - $start;
        $this->Logger->writeToLog($statement, $this->userId, $time);
        return $result;
    }

    public function setCurrentMustHaveResult($mustHaveRow = true) {
        $this->currentMustHaveRow = (bool)$mustHaveRow;
    }

    public function handle(PdoStatement $Statement, Entity $Entity) {
        $mustHaveRow = $this->currentMustHaveRow;
        $this->setCurrentMustHaveResult(false);
        if ($this->execute($Statement)) {
            if ($Statement->rowCount() === 0 && $mustHaveRow) {
                throw new NotFoundHttpException("No row found with query");
            }
            return $this->getMapper()->mapFromResult($Statement, $Entity);
        }
        return $this->handleError($Statement);
    }

    public function handleArray(PdoStatement $Statement, Entity $Entity, \Closure $Closure) {
        $mustHaveRow = $this->currentMustHaveRow;
        $this->setCurrentMustHaveResult(false);
        if ($this->execute($Statement)) {
            if ($Statement->rowCount() === 0 && $mustHaveRow) {
                throw new NotFoundHttpException("No row found with query");
            }
            return $this->getMapper()->mapToArray($Statement, $Entity, $Closure);
        }
        return $this->handleError($Statement);
    }

    /**
     * Handles statement errors
     *
     * @param PdoStatement $statement
     * @return bool
     * @throws DatabaseException
     * @throws ForeignKeyConstraintException
     * @throws UniqueConstraintException
     */
    protected function handleError(PdoStatement $statement) {
        return $this->ErrorHandler->handle($statement->errorInfo()[1], $statement->errorInfo()[2]);
    }

    /**
     * Validates whether the offset is greater or equal to zero
     * @param $offset int the offset to validate
     * @return int
     */
    public function validateOffset($offset) {
        if ($offset < 0 ) {
            return 0;
        }
        return (int)$offset;
    }

    /**
     * Validates whether the limit is greater than 1 and less than $max
     * @param $limit int the limit to validate
     * @param $max int the max limit allowed
     * @return int the validated limit as an integer
     */
    public function validateLimit($limit, $max = 100) {
        if ($limit < 1) {
            return 1;
        } elseif ($limit > $max) {
            return $max;
        }
        return (int)$limit;
    }

    /**
     * Checks whether there exists a unique row with the given ids<br />
     * <br />
     * It is important that you limit your query to one, if there is more than one row, the function will return false<br />
     * If you provide an array as $id, all values will be bound to the statement as "id{key}", where key indicates
     *  the array key + 1
     *
     * @param PdoStatement $Statement the statement to work with
     * @param $id array|int an array of ids or an id
     * @return bool whether there is such a row or not
     */
    protected function _handleHas(PdoStatement $Statement, $id) {
        if (!is_array($id)) {
            $Statement->bindValue('id', $id, \PDO::PARAM_INT);
        } else {
            foreach ($id as $key => $anId) {
                $Statement->bindValue('id' . (++$key), $anId);
            }
        }
        if ($this->execute($Statement) && $Statement->rowCount() == 1) {
            return true;
        }
        return $this->handleError($Statement);
    }

    /**
     * Validates whether the given statement has result rows or not<br /
     * <br />
     * Also executes this statement, so do not execute before!
     * @param PdoStatement $Statement
     * @return bool whether there is at least one result row or not
     */
    protected function _handleHasResult(PdoStatement $Statement) {
        if ($this->execute($Statement)) {
            if ($Statement->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Begins a new transaction if not already in one
     * @throws TransactionException
     */
    protected function _startTransaction() {
        if (!$this->getPDO()->inTransaction()) {
            if (!$this->getPDO()->beginTransaction()) {
                throw new TransactionException("Unable to start transaction");
            }
        }
    }

    /**
     * Commits the actual transaction if one is started
     *
     * Throws an exception if no transaction is running
     * @throws TransactionException
     */
    protected function _commit() {
        if ($this->getPDO()->inTransaction()) {
            if (!$this->getPDO()->commit()) {
                throw new TransactionException("Unable to commit");
            }
        } else {
            throw new TransactionException("No transaction running");
        }
    }

    /**
     * Rolls the actual transaction back
     *
     * Throws an exception if no transaction is running
     * @throws TransactionException
     */
    protected function _rollback() {
        if ($this->getPDO()->inTransaction()) {
            if (!$this->getPDO()->rollBack()) {
                throw new TransactionException("Unable to rollback");
            }
        } else {
            throw new TransactionException("No transaction running");
        }
    }
}
