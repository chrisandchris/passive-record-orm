<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;
use ChrisAndChris\Common\RowMapperBundle\Entity\KeyValueEntity;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\DatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\ForeignKeyConstraintException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\TransactionException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\UniqueConstraintException;
use ChrisAndChris\Common\RowMapperBundle\Services\Mapper\RowMapper;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @name Model
 * @version    2.1.1
 * @lastChange v2.1.0
 * @since      v1.0.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 * @deprecated v2.1.0; inject ConcreteModel class instead; not to be removed soon (not earlier than 3.0.0!)
 */
abstract class Model
{

    /** @var string a id representing the current user */
    private static $userId;
    /** @var ModelDependencyProvider the dependency provider */
    protected $dependencyProvider;
    /** @var bool if set to true, current result must have at least one row */
    private $currentMustHaveRow;
    /** @var PdoStatement */
    protected $lastStatement = null;

    function __construct(ModelDependencyProvider $dependencyProvider)
    {
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * @return string
     * @deprecated v2.1.0, to be removed in v2.2.0
     */
    public static function getRunningUser()
    {
        return self::$userId;
    }

    /**
     * Set the id of the current user for logging purposes
     *
     * @param $userId
     * @deprecated v2.1.0, to be removed in v2.2.0
     */
    public function setRunningUser($userId)
    {
        self::$userId = $userId;
    }

    /**
     * Validates whether the offset is greater or equal to zero
     *
     * @param $offset int the offset to validate
     * @return int
     */
    public function validateOffset($offset)
    {
        if ($offset < 0) {
            return 0;
        }

        return (int)$offset;
    }

    /**
     * Validates whether the limit is greater than 1 and less than $max
     *
     * @param $limit int the limit to validate
     * @param $max   int the max limit allowed
     * @return int the validated limit as an integer
     */
    public function validateLimit($limit, $max = 100)
    {
        if ($limit < 1) {
            return 1;
        } elseif ($limit > $max) {
            return $max;
        }

        return (int)$limit;
    }

    /**
     * Prepares the option array
     *
     * @param array $availableOptions
     * @param array $options
     * @throws InvalidOptionException
     */
    public function prepareOptions(array $availableOptions, array &$options)
    {
        foreach ($availableOptions as $option) {
            if (!isset($options[$option])) {
                $options[$option] = null;
            }
        }
        foreach (array_keys($options) as $name) {
            if (!in_array($name, $availableOptions)) {
                throw new InvalidOptionException(
                    "Option '" . $name .
                    "' is unknown to this method."
                );
            }
        }
    }

    /**
     * Runs a query
     *
     * @param SqlQuery $query
     * @param Entity   $entity
     * @param \Closure $callAfter call after successful run of query
     * @return bool|Entity[] $entity[]
     */
    protected function run(SqlQuery $query, Entity $entity, \Closure $callAfter = null)
    {
        $stmt = $this->prepare($query);

        $result = $this->handle($stmt, $entity);
        if ($callAfter instanceof \Closure) {
            $callAfter($result);
        }

        return $result;
    }

    /**
     * Prepares a statement including value binding
     *
     * @param SqlQuery $query
     * @return PdoStatement
     */
    protected function prepare(SqlQuery $query)
    {
        var_dump($query->isCalcRowCapable());

        $stmt = $this->createStatement($query->getQuery());
        $this->bindValues($stmt, $query);
        $stmt->requiresResult($query->isResultRequired());
        $stmt->setCalcRowCapable($query->isCalcRowCapable());

        $this->lastStatement = $stmt;

        return $stmt;
    }

    /**
     * Create a new statement from SQL-Code
     *
     * @param $sql
     * @return PdoStatement
     */
    private function createStatement($sql)
    {
        return $this->getDependencyProvider()
                    ->getPdo()
                    ->prepare($sql);
    }

    /**
     * Get the dependency provider
     *
     * @return ModelDependencyProvider
     */
    protected function getDependencyProvider()
    {
        return $this->dependencyProvider;
    }

    /**
     * Binds values of the query to the statement
     *
     * @param PdoStatement $stmt
     * @param SqlQuery     $query
     */
    private function bindValues(PdoStatement $stmt, SqlQuery $query)
    {
        foreach ($query->getParameters() as $id => $value) {
            $stmt->bindValue(++$id, $value);
        }
    }

    /**
     * Handles a statement including mapping to entity (if given) and error
     * handling<br /> If no entity is given returns true on success, false
     * otherwise
     *
     * @param PdoStatement $statement
     * @param Entity       $entity
     * @return Entity[]|bool
     */
    private function handle(PdoStatement $statement, Entity $entity = null)
    {
        return $this->handleGeneric(
            $statement,
            function (PdoStatement $statement) use ($entity) {
                if ($entity === null) {
                    if ((int)$statement->errorCode() == 0) {
                        return true;
                    }

                    return false;
                }

                return $this->getMapper()
                            ->mapFromResult($statement, $entity);
            }
        );
    }

    /**
     * Generic handle method
     *
     * @param PdoStatement $statement
     * @param \Closure     $mappingCallback      a callback taking the
     *                                           statement as first and only
     *                                           argument
     * @return bool
     */
    private function handleGeneric(PdoStatement $statement, \Closure $mappingCallback)
    {
        $mustHaveRow = $this->currentMustHaveRow;
        $this->setCurrentMustHaveResult(false);
        if ($this->execute($statement)) {
            if ($statement->rowCount() === 0 &&
                ($mustHaveRow || $statement->isResultRequired())
            ) {
                throw new NotFoundHttpException("No row found with query");
            }

            return $mappingCallback($statement);
        }

        return $this->handleError($statement);
    }

    /**
     * Set to true if current statement must have at least one row returning
     *
     * @param bool $mustHaveRow
     * @deprecated v2.1.0, to be removed in v2.2.0, use SqlQuery::
     */
    protected function setCurrentMustHaveResult($mustHaveRow = true)
    {
        $this->currentMustHaveRow = (bool)$mustHaveRow;
    }

    /**
     * Execute a PDOStatement and writes it to the log
     *
     * @param PdoStatement $statement
     * @return mixed
     */
    private function execute(PdoStatement $statement)
    {
        $result = $statement->execute();

        return $result;
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
    private function handleError(PdoStatement $statement)
    {
        if ($this->getDependencyProvider()
                 ->getPdo()
                 ->inTransaction()
        ) {
            // automatic rollback the transaction if an error occurs
            $this->_rollback();
        }

        return $this->getErrorHandler()
                    ->handle(
                        $statement->errorInfo()[1],
                        $statement->errorInfo()[2]
                    );
    }

    /**
     * Rolls the actual transaction back
     *
     * Throws an exception if no transaction is running
     *
     * @throws TransactionException if unable to rollback
     */
    protected function _rollback()
    {
        if (!$this->_inTransaction()) {
            return;
        }
        if (!$this->getDependencyProvider()
                  ->getPdo()
                  ->rollBack()
        ) {
            throw new TransactionException("Unable to rollback");
        }
    }

    /**
     * Returns whether a transaction is running or not
     *
     * @return bool
     */
    protected function _inTransaction()
    {
        return $this->getDependencyProvider()
                    ->getPdo()
                    ->inTransaction();
    }

    /**
     * Get the error handler
     *
     * @return ErrorHandler
     */
    protected function getErrorHandler()
    {
        return $this->dependencyProvider->getErrorHandler();
    }

    /**
     * Get the Mapper
     *
     * @return RowMapper
     */
    protected function getMapper()
    {
        return $this->dependencyProvider->getMapper();
    }

    /**
     * Get the dependency provider (shortcut)
     *
     * @return ModelDependencyProvider
     */
    protected function getDp()
    {
        return $this->dependencyProvider;
    }

    /** @noinspection PhpDocSignatureInspection */

    /**
     * Run a query with custom return
     *
     * @todo   needs testing
     *
     * @param SqlQuery       $query
     * @param mixed|\Closure $onSuccess on success
     * @param mixed|\Closure $onFailure on failure
     * @param null|\Closure  $onError   on exception, if null exception is
     *                                  thrown
     * @return $onSuccess|$onFailure|$onError
     * @throws \Exception
     */
    protected function runCustom(SqlQuery $query, $onSuccess, $onFailure, $onError = null)
    {
        try {
            if ($this->runSimple($query)) {
                if ($onSuccess instanceof \Closure) {
                    return $onSuccess();
                }

                return $onSuccess;
            } else {
                if ($onFailure instanceof \Closure) {
                    return $onFailure();
                }

                return $onFailure;
            }
        } catch (\Exception $exception) {
            if ($onError === null) {
                throw $exception;
            } else {
                if ($onError instanceof \Closure) {
                    return $onError();
                }
            }

            return $onError;
        }
    }

    /**
     * Runs a simple query, just returning true on success
     *
     * @param SqlQuery $query
     * @return bool
     */
    protected function runSimple(SqlQuery $query)
    {
        return $this->handle($this->prepare($query), null);
    }

    /**
     * Runs a simple query, returning the last insert id on success
     *
     * @param SqlQuery $query
     * @return int
     */
    protected function runWithLastId(SqlQuery $query)
    {
        return $this->handleWithLastInsertId($this->prepare($query));
    }

    /**
     * Handles a statement and returns the last insert id on success
     *
     * @param PdoStatement $statement
     * @return int
     */
    private function handleWithLastInsertId(PdoStatement $statement)
    {
        return $this->handleGeneric(
            $statement,
            function () {
                return $this->getDependencyProvider()
                            ->getPdo()
                            ->lastInsertId();
            }
        );
    }

    /**
     * Call query and get first column of first row
     *
     * @param SqlQuery $query
     * @return mixed
     */
    protected function runWithFirstKeyFirstValue(SqlQuery $query)
    {
        $stmt = $this->prepare($query);

        return $this->handleGeneric(
            $stmt, function (PdoStatement $statement) {
            if ($statement->rowCount() > 1) {
                throw new DatabaseException(sprintf(
                    'Expected only a single result record, but got %d',
                    $statement->rowCount()
                ));
            }

            return $statement->fetch(\PDO::FETCH_NUM)[0];
        }
        );
    }

    /**
     * Handles an array query
     *
     * @param SqlQuery $query
     * @param Entity   $entity
     * @param \Closure $closure
     * @return array
     */
    protected function runArray(SqlQuery $query, Entity $entity, \Closure $closure)
    {
        return $this->handleGeneric(
            $this->prepare($query),
            function (PdoStatement $statement) use ($entity, $closure) {
                return $this->getMapper()
                            ->mapToArray($statement, $entity, $closure);
            }
        );
    }

    /**
     * Runs the query and maps it to an associative array
     *
     * @param SqlQuery $query
     * @return array
     */
    protected function runAssoc(SqlQuery $query)
    {
        return $this->handleGeneric(
            $this->prepare($query),
            function (\PDOStatement $statement) {
                return $this->getMapper()
                            ->mapFromResult($statement);
            }
        );
    }

    /**
     * Handles a key value query
     *
     * @param SqlQuery $query
     * @return array
     */
    protected function runKeyValue(SqlQuery $query)
    {
        $stmt = $this->prepare($query);

        return $this->handleGeneric(
            $stmt,
            function (PdoStatement $statement) {
                return $this->getMapper()
                            ->mapToArray(
                                $statement, new KeyValueEntity(),
                                function (KeyValueEntity $entity) {
                                    static $count = 0;
                                    if (empty($entity->key)) {
                                        $entity->key = $count++;
                                    }

                                    return [
                                        'key'   => $entity->key,
                                        'value' => $entity->value,
                                    ];
                                }
                            );
            }
        );
    }

    /**
     * Validates whether the given statement has a row count greater than zero
     *
     * @param SqlQuery $query
     * @return bool whether there is at least one result row or not
     */
    protected function _handleHasResult(SqlQuery $query)
    {
        return $this->_handleHas($query, false);
    }

    /**
     * Runs the query and returns whether the row count is equal to one or not
     *
     * @param SqlQuery $query      the query
     * @param bool     $forceEqual if set to true, only a row count of one and
     *                             only one returns true
     * @return bool whether there is a row or not
     */
    protected function _handleHas(SqlQuery $query, $forceEqual = true)
    {
        $stmt = $this->prepare($query);

        return $this->handleGeneric(
            $stmt,
            function (PdoStatement $Statement) use ($forceEqual) {
                if ($Statement->rowCount() == 1 && $forceEqual) {
                    return true;
                } else {
                    if ($Statement->rowCount() > 0 && !$forceEqual) {
                        return true;
                    }
                }

                return false;
            }
        );
    }

    /**
     * Begins a new transaction if not already in one
     *
     * @throws TransactionException
     */
    protected function _startTransaction()
    {
        if (!$this->getDependencyProvider()
                  ->getPdo()
                  ->inTransaction()
        ) {
            if (!$this->getDependencyProvider()
                      ->getPdo()
                      ->beginTransaction()
            ) {
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
    protected function _commit()
    {
        if ($this->getDependencyProvider()
                 ->getPdo()
                 ->inTransaction()
        ) {
            if (!$this->getDependencyProvider()
                      ->getPdo()
                      ->commit()
            ) {
                throw new TransactionException("Unable to commit");
            }
        } else {
            throw new TransactionException("No transaction running");
        }
    }

    /**
     * Checks whether $optionName is the only option which is not null, except for keys of $allowAlways
     *
     * @param array $options
     * @param       $optionName
     * @param null  $expectedValue
     * @param array $allowAlways
     * @return bool
     */
    public function isOnlyOption(
        array $options,
        $optionName,
        $expectedValue = null,
        array $allowAlways = ['limit', 'offset', 'pagination']
    ) {
        foreach ($options as $name => $value) {
            if ($name != $optionName && $value !== null && !in_array($name, $allowAlways)) {
                return false;
            }
        }

        if ($expectedValue !== null) {
            return true && array_key_exists($optionName, $options) && $options[$optionName] == $expectedValue;
        }

        return true && array_key_exists($optionName, $options) && $options[$optionName] !== null;
    }
}
