<?php
namespace ChrisAndChris\Common\RowMapperBundle\Services\Model;

use ChrisAndChris\Common\RowMapperBundle\Entity\Entity;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\SqlQuery;

/**
 * @name ConcreteModel
 * @version    1
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 * @todo       needs some testing
 */
class ConcreteModel extends Model
{

    public function __construct(ModelDependencyProvider $dependencyProvider)
    {
        parent::__construct($dependencyProvider);
    }

    /**
     * @inheritDoc
     */
    public function validateOffset($offset)
    {
        return parent::validateOffset($offset);
    }

    /**
     * @inheritDoc
     */
    public function validateLimit($limit, $max = 100)
    {
        return parent::validateLimit($limit, $max);
    }

    /**
     * @inheritDoc
     */
    public function prepareOptions(array $availableOptions, array &$options)
    {
        parent::prepareOptions($availableOptions, $options);
    }

    /**
     * @inheritDoc
     */
    public function run(SqlQuery $query, Entity $entity)
    {
        return parent::run($query, $entity);
    }

    /**
     * @inheritDoc
     */
    public function prepare(SqlQuery $query)
    {
        return parent::prepare($query);
    }

    /**
     * @inheritDoc
     */
    public function getDependencyProvider()
    {
        return parent::getDependencyProvider();
    }

    /**
     * @inheritDoc
     */
    public function getDp()
    {
        return parent::getDp();
    }

    /**
     * @inheritDoc
     */
    public function rollback()
    {
        parent::_rollback();
    }

    /**
     * @inheritDoc
     */
    public function inTransaction()
    {
        return parent::_inTransaction();
    }

    /**
     * @inheritDoc
     */
    public function getErrorHandler()
    {
        return parent::getErrorHandler();
    }

    /**
     * @inheritDoc
     */
    public function getMapper()
    {
        return parent::getMapper();
    }

    /**
     * @inheritDoc
     */
    public function runCustom(SqlQuery $query, $onSuccess, $onFailure, $onError = null)
    {
        return parent::runCustom($query, $onSuccess, $onFailure, $onError);
    }

    /**
     * @inheritDoc
     */
    public function runSimple(SqlQuery $query)
    {
        return parent::runSimple($query);
    }

    /**
     * @inheritDoc
     */
    public function runWithLastId(SqlQuery $query)
    {
        return parent::runWithLastId($query);
    }

    /**
     * @inheritDoc
     */
    public function runWithFirstKeyFirstValue(SqlQuery $query)
    {
        return parent::runWithFirstKeyFirstValue($query);
    }

    /**
     * @inheritDoc
     */
    public function runArray(SqlQuery $query, Entity $entity, \Closure $closure)
    {
        return parent::runArray($query, $entity, $closure);
    }

    /**
     * @inheritDoc
     */
    public function runAssoc(SqlQuery $query)
    {
        return parent::runAssoc($query);
    }

    /**
     * @inheritDoc
     */
    public function runKeyValue(SqlQuery $query)
    {
        return parent::runKeyValue($query);
    }

    /**
     * @inheritDoc
     */
    public function handleHasResult(SqlQuery $query)
    {
        return parent::_handleHasResult($query);
    }

    /**
     * @inheritDoc
     */
    public function handleHas(SqlQuery $query, $forceEqual = true)
    {
        return parent::_handleHas($query, $forceEqual);
    }

    /**
     * @inheritDoc
     */
    public function startTransaction()
    {
        parent::_startTransaction();
    }

    /**
     * @inheritDoc
     */
    public function commit()
    {
        parent::_commit();
    }

}
