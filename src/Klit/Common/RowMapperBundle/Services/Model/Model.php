<?php
namespace Klit\Common\RowMapperBundle\Services\Model;

use Klit\Common\RowMapperBundle\Entity\Entity;
use Klit\Common\RowMapperBundle\Entity\KeyValueEntity;
use Klit\Common\RowMapperBundle\Exceptions\DatabaseException;
use Klit\Common\RowMapperBundle\Exceptions\ForeignKeyConstraintException;
use Klit\Common\RowMapperBundle\Exceptions\TransactionException;
use Klit\Common\RowMapperBundle\Exceptions\UniqueConstraintException;
use Klit\Common\RowMapperBundle\Services\Logger\LoggerInterface;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoStatement;
use Klit\Common\RowMapperBundle\Services\Pdo\RowMapper;
use Klit\Common\RowMapperBundle\Services\Query\SqlQuery;
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
    /** @var bool if set to true, current result must have at least one row */
    private $currentMustHaveRow;
    /** @var string a id representing the current user */
    private static $userId;
    /** @var ModelDependencyProvider the dependency provider */
    protected $DependencyProvider;

    function __construct(ModelDependencyProvider $DependencyProvider) {
        $this->DependencyProvider = $DependencyProvider;
    }

    /**
     * Set the id of the current user for logging purposes
     *
     * @param $userId
     */
    public function setRunningUser($userId) {
        self::$userId = $userId;
    }

    /**
     * @return string
     */
    public static function getRunningUser() {
        return self::$userId;
    }

    /**
     * Get the dependency provider
     *
     * @return ModelDependencyProvider
     */
    protected function getDependencyProvider() {
        return $this->DependencyProvider;
    }

    /**
     * Get the PDO class
     *
     * @return \PDO
     */
    protected  function getPDO() {
        return $this->DependencyProvider->getPDO();
    }

    /**
     * Get the Mapper
     *
     * @return RowMapper
     */
    protected  function getMapper() {
        return $this->DependencyProvider->getMapper();
    }

    /**
     * Create a new statement from SQL-Code
     *
     * @param $sql
     * @return PdoStatement
     */
    protected function createStatement($sql) {
        return $this->getPDO()->prepare($sql);
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    protected function getLogger() {
        return $this->DependencyProvider->getLogger();
    }

    /**
     * Get the error handler
     *
     * @return ErrorHandler
     */
    protected function getErrorHandler() {
        return $this->DependencyProvider->getErrorHandler();
    }

    /**
     * Binds values of the query to the statement
     *
     * @param PdoStatement $stmt
     * @param SqlQuery $Query
     */
    protected function bindValues(PdoStatement $stmt, SqlQuery $Query) {
        foreach ($Query->getParameters() as $id => $value) {
            $stmt->bindValue(++$id, $value);
        }
    }

    /**
     * Set to true if current statement must have at least one row returning
     *
     * @param bool $mustHaveRow
     */
    protected function setCurrentMustHaveResult($mustHaveRow = true) {
        $this->currentMustHaveRow = (bool)$mustHaveRow;
    }

    /**
     * Runs a query
     *
     * @param SqlQuery $Query
     * @param Entity $Entity
     * @return array|bool
     */
    protected function run(SqlQuery $Query, Entity $Entity) {
        $stmt = $this->prepare($Query);
        return $this->handle($stmt, $Entity);
    }

    /**
     * Runs a simple query, just returning true on success
     *
     * @param SqlQuery $Query
     * @return bool|\Klit\Common\RowMapperBundle\Entity\Entity[]
     */
    protected function runSimple(SqlQuery $Query) {
        return $this->handle($this->prepare($Query), null);
    }

    /**
     * Runs a simple query, returning the last insert id on success
     *
     * @param SqlQuery $Query
     * @return bool|int
     */
    protected function runWithLastId(SqlQuery $Query) {
        return $this->handleWithLastInsertId($this->prepare($Query));
    }

    /**
     * Handles an array query
     *
     * @param SqlQuery $Query
     * @param Entity $Entity
     * @param callable $Closure
     * @return array|bool
     */
    protected function runArray(SqlQuery $Query, Entity $Entity, \Closure $Closure) {
        $stmt = $this->prepare($Query);
        return $this->handleArray($stmt, $Entity, $Closure);
    }

    /**
     * Handles a key value query
     *
     * @param SqlQuery $Query
     * @return bool
     */
    protected function runKeyValue(SqlQuery $Query) {
        $stmt = $this->prepare($Query);
        return $this->handleKeyValue($stmt);
    }

    /**
     * Prepares a statement including value binding
     *
     * @param SqlQuery $Query
     * @return PdoStatement
     */
    protected function prepare(SqlQuery $Query) {
        $stmt = $this->createStatement($Query->getQuery());
        $this->bindValues($stmt, $Query);
        return $stmt;
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
        $this->getLogger()->writeToLog($statement, self::$userId, $time);
        return $result;
    }

    /**
     * Generic handle method
     *
     * @param PdoStatement $Statement
     * @param callable $MappingCallback a callback taking the statement as first and only argument
     * @return bool
     */
    protected function handleGeneric(PdoStatement $Statement, \Closure $MappingCallback) {
        $mustHaveRow = $this->currentMustHaveRow;
        $this->setCurrentMustHaveResult(false);
        if ($this->execute($Statement)) {
            if ($Statement->rowCount() === 0 && ($mustHaveRow || $Statement->isMustHaveResult())) {
                throw new NotFoundHttpException("No row found with query");
            }
            return $MappingCallback($Statement);
        }
        return $this->handleError($Statement);
    }

    /**
     * Handles a statement including mapping to entity (if given) and error handling<br />
     * If no entity is given returns true on success, false otherwise
     *
     * @param PdoStatement $Statement
     * @param Entity $Entity
     * @return Entity[]|bool
     */
    protected function handle(PdoStatement $Statement, Entity $Entity = null) {
        return $this->handleGeneric($Statement, function (PdoStatement $Statement) use ($Entity) {
            if ($Entity === null) {
                if ((int)$Statement->errorCode() == 0) {
                    return true;
                }
                return false;
            }
            return $this->getMapper()->mapFromResult($Statement, $Entity);
        });
    }

    /**
     * Call query and get first column of first row
     *
     * @param $Query
     * @return bool
     */
    protected function runWithFirstKeyFirstValue($Query) {
        $stmt = $this->prepare($Query);
        return $this->handleGeneric($stmt, function (PdoStatement $Statement) {
            return $Statement->fetch(\PDO::FETCH_NUM)[0];
        });
    }

    /**
     * Handles a statement and returns the last insert id on success
     *
     * @param PdoStatement $Statement
     * @return bool|int
     */
    protected function handleWithLastInsertId(PdoStatement $Statement) {
        return $this->handleGeneric($Statement, function (\PDOStatement $Statement) {
            return $this->getPDO()->lastInsertId();
        });
    }

    /**
     * Returns the first column of the first row or a NotFoundHttpException if no row available
     *
     * @param PdoStatement $Statement
     * @return bool
     */
    protected function handleWithFirstRowFirstColumn(PdoStatement $Statement) {
        $Statement->setMustHaveResult();
        return $this->handleGeneric($Statement, function (PdoStatement $Statement) {
            return $Statement->fetch(\PDO::FETCH_NUM)[0];
        });
    }
    /**
     * Handles a statement including mapping to array and error handling
     *
     * @param PdoStatement $Statement
     * @param Entity $Entity
     * @param callable $Closure
     * @return array|bool
     * @throws \Symfony\Component\Debug\Exception\FatalErrorException
     */
    protected function handleArray(PdoStatement $Statement, Entity $Entity, \Closure $Closure) {
        return $this->handleGeneric($Statement, function (PdoStatement $Statement) use ($Entity, $Closure) {
            return $this->getMapper()->mapToArray($Statement, $Entity, $Closure);
        });
    }

    /**
     * Maps the statement to a key => value array<br />
     * <br />
     * Use SQL-Field 'key' for array key, 'value' for array value
     *
     * @param PdoStatement $Statement
     * @return bool
     */
    protected function handleKeyValue(PdoStatement $Statement) {
        return $this->handleGeneric($Statement, function (PdoStatement $Statement) {
            return $this->getMapper()->mapToArray($Statement, new KeyValueEntity(), function (KeyValueEntity $Entity) {
                return array(
                    'key' => $Entity->key,
                    'value' => $Entity->value
                );
            });
        });
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
        if ($this->getPDO()->inTransaction()) {
            // automatic rollback the transaction if an error occurs
            $this->_rollback();
        }
        return $this->getErrorHandler()->handle($statement->errorInfo()[1], $statement->errorInfo()[2]);
    }

    /**
     * Validates whether the offset is greater or equal to zero
     *
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
     *
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
     * Runs the query and returns whether the row count is equal to one or not
     *
     * @param SqlQuery $Query the query
     * @param bool $forceEqual if set to true, only a row count of one and only one returns true
     * @return bool whether there is a row or not
     */
    protected function _handleHas(SqlQuery $Query, $forceEqual = true) {
        $stmt = $this->prepare($Query);
        return $this->handleGeneric($stmt, function (PdoStatement $Statement) use ($forceEqual) {
            if ($Statement->rowCount() == 1 && $forceEqual) {
                return true;
            } else if ($Statement->rowCount() > 0 && !$forceEqual) {
                return true;
            }
            return false;
        });
    }

    /**
     * Validates whether the given statement has a row count greater than zero
     *
     * @param SqlQuery $Query
     * @return bool whether there is at least one result row or not
     */
    protected function _handleHasResult(SqlQuery $Query) {
        return $this->_handleHas($Query, false);
    }

    /**
     * Begins a new transaction if not already in one
     *
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
     *
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
     *
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
