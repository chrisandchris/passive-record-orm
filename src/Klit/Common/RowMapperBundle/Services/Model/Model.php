<?php
namespace Klit\Common\RowMapperBundle\Services\Model;

use Klit\Common\RowMapperBundle\Exceptions\DatabaseException;
use Klit\Common\RowMapperBundle\Exceptions\ForeignKeyConstraintException;
use Klit\Common\RowMapperBundle\Exceptions\TransactionException;
use Klit\Common\RowMapperBundle\Exceptions\UniqueConstraintException;
use Klit\Common\RowMapperBundle\Services\Pdo\PdoLayer;
use Klit\Common\RowMapperBundle\Services\Pdo\RowMapper;

/**
 * @name Model
 * @version 1.0.0
 * @package CommonRowMapperBundle
 * @author Christian Klauenbösch <christian@klit.ch>
 * @copyright Klauenbösch IT Services
 * @link http://www.klit.ch
 */
abstract class Model {
    /** @var PdoLayer pdo */
    private $PDO;
    private $mapper;

    function __construct(PdoLayer $PDO, RowMapper $mapper) {
        $this->PDO = $PDO;
        $this->mapper = $mapper;
    }

    /**
     * @param \PDO $PDO
     * @deprecated
     */
    protected function setPdo(\PDO $PDO) {
        $this->PDO = $PDO;
    }

    /**
     * @param \Klit\Common\RowMapperBundle\Services\Pdo\RowMapper $mapper
     */
    protected function setMapper($mapper) {
        $this->mapper = $mapper;
    }

    /**
     * @return \PDO
     */
    protected  function getPDO() {
        return $this->PDO;
    }

    /**
     * @return RowMapper
     */
    protected  function getMapper() {
        return $this->mapper;
    }

    /**
     * @param $sql
     * @return \PDOStatement
     */
    protected function createStatement($sql) {
        return $this->PDO->prepare($sql);
    }

    protected function execute(\PDOStatement $statement) {
        return $this->PDO->execute($statement);
    }

    /**
     * @param \PDOStatement $statement
     * @return bool
     * @throws DatabaseException
     * @throws ForeignKeyConstraintException
     * @throws UniqueConstraintException
     */
    protected function handleError(\PDOStatement $statement) {
        $errorNum = $statement->errorInfo();
        $errorText = $errorNum[2];
        if ($errorNum[1] === null AND $errorNum[0] !== 'HY093') {
            // Well, the PDOStatement::execute() method returned false, we do it also
            return false;
        } elseif ($errorNum[0] == 'HY093') {
            throw new DatabaseException('incorrect field <-> param count');
        } else {
            $e = &$errorNum[1];
            if ($e == 1062) {
                throw new UniqueConstraintException($errorText);
            } elseif ($e == 1064) {
                // sql syntax
                throw new DatabaseException($errorText);
            } elseif ($e == 1215
                OR $e == 1216
                OR $e == 1217) {
                throw new ForeignKeyConstraintException($errorText);
            }
        }
        throw new DatabaseException($errorText);
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
     * @param \PDOStatement $statement the statement to work with
     * @param $id array|int an array of ids or an id
     * @return bool whether there is such a row or not
     */
    protected function _handleHas(\PDOStatement $statement, $id) {
        if (!is_array($id)) {
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
        } else {
            foreach ($id as $key => $anId) {
                $statement->bindValue('id' . (++$key), $anId);
            }
        }
        if ($statement->execute() && $statement->rowCount() == 1) {
            return true;
        }
        return $this->handleError($statement);
    }

    /**
     * Validates whether the given statement has result rows or not<br /
     * <br />
     * Also executes this statement, so do not execute before!
     * @param \PDOStatement $statement
     * @return bool whether there is at least one result row or not
     */
    protected function _handleHasResult(\PDOStatement $statement) {
        if ($statement->execute()) {
            if ($statement->rowCount() > 0) {
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
 