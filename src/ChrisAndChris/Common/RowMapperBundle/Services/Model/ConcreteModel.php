<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;
use ChrisAndChris\Common\RowMapperBundle\Entity\KeyValueEntity;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\Database\NoSuchRowFoundException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\DatabaseException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\ForeignKeyConstraintException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\InvalidOptionException;
use ChrisAndChris\Common\RowMapperBundle\Exceptions\UniqueConstraintException;
use ChrisAndChris\Common\RowMapperBundle\Services\Pdo\PdoStatement;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery;

/**
 * @name ConcreteModel
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
class ConcreteModel
{

    /** @var ModelDependencyProvider the dependency provider */
    protected $dependencyProvider;

    function __construct(ModelDependencyProvider $dependencyProvider)
    {
        $this->dependencyProvider = $dependencyProvider;
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
                throw new InvalidOptionException(sprintf(
                    "Option '%s' is unknown to this method",
                    $name
                ));
            }
        }
    }

    /** @noinspection PhpDocSignatureInspection */
    /**
     * Runs a query
     *
     * @param SqlQuery $query
     * @param Entity   $entity
     * @return $entity[]
     */
    protected function run(SqlQuery $query, Entity $entity)
    {
        $stmt = $this->prepare($query);

        return $this->handle($stmt, $entity);
    }

    /**
     * Prepares a statement including value binding
     *
     * @param SqlQuery $query
     * @return PdoStatement
     */
    protected function prepare(SqlQuery $query)
    {
        $stmt = $this->createStatement($query->getQuery());
        foreach ($query->getParameters() as $id => $parameter) {
            $bindType = \PDO::PARAM_STR;
            if ($parameter === true || $parameter === false) {
                $bindType = \PDO::PARAM_BOOL;
            } elseif ($parameter === null) {
                $bindType = \PDO::PARAM_NULL;
            } elseif (is_numeric($parameter)) {
                $bindType = \PDO::PARAM_INT;
            }
            $stmt->bindValue(++$id, $parameter, $bindType);
        }
        $stmt->requiresResult($query->isResultRequired());

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
     * @throws NoSuchRowFoundException
     */
    private function handleGeneric(PdoStatement $statement, \Closure $mappingCallback)
    {
        if ($this->execute($statement)) {
            if ($statement->rowCount() === 0 && $statement->isResultRequired()) {
                throw new NoSuchRowFoundException("No row found with query");
            }

            return $mappingCallback($statement);
        }

        return $this->handleError($statement);
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
        return $this->getErrorHandler()
                    ->handle(
                        $statement->errorInfo()[1],
                        $statement->errorInfo()[2]
                    );
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
     * @param string   $sequence the sequence to return the last insert id for
     * @return int
     */
    protected function runWithLastId(SqlQuery $query, $sequence = null)
    {
        return $this->handleWithLastInsertId($this->prepare($query), $sequence);
    }

    /**
     * Handles a statement and returns the last insert id on success
     *
     * @param PdoStatement $statement
     * @param string       $sequence the sequence to return the last insert id for
     * @return int
     */
    private function handleWithLastInsertId(PdoStatement $statement, $sequence = null)
    {
        return $this->handleGeneric(
            $statement,
            function () use ($sequence) {

                if (strstr($sequence, ':')) {
                    $sequence = explode(':', $sequence);
                    array_push($sequence, 'seq');
                    $sequence = implode('_', $sequence);
                }

                return $this->getDependencyProvider()
                            ->getPdo()
                            ->lastInsertId($sequence);
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
    protected function handleHasResult(SqlQuery $query)
    {
        return $this->handleHas($query, false);
    }

    /**
     * Runs the query and returns whether the row count is equal to one or not
     *
     * @param SqlQuery $query      the query
     * @param bool     $forceEqual if set to true, only a row count of one and
     *                             only one returns true
     * @return bool whether there is a row or not
     */
    protected function handleHas(SqlQuery $query, $forceEqual = true)
    {
        $stmt = $this->prepare($query);

        return $this->handleGeneric(
            $stmt,
            function (PdoStatement $statement) use ($forceEqual) {
                if ($statement->rowCount() == 1 && $forceEqual) {
                    return true;
                } else {
                    if ($statement->rowCount() > 0 && !$forceEqual) {
                        return true;
                    }
                }

                return false;
            }
        );
    }
}
