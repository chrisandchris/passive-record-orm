<?php
declare(strict_types=1);

namespace ChrisAndChris\Common\RowMapperBundle\Tests\Services\Relation;

use ChrisAndChris\Common\RowMapperBundle\Entity\Relation\ResultSet;
use ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder;
use ChrisAndChris\Common\RowMapperBundle\Tests\Services\Relation\Demo\InvoiceDemoEntity;
use ChrisAndChris\Common\RowMapperBundle\Tests\Services\Relation\Demo\OperationDemoEntity;

/**
 *
 *
 * @name RelatedRepo
 * @version   1.0.0
 * @since     1.0.0
 * @package   RowMapperBundle
 * @author    ChrisAndChris
 * @link      https://github.com/chrisandchris
 */
class DemoRelatedRepo
{

    /**
     * @var \ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder
     */
    private $builder;


    /**
     * DemoRelatedRepo constructor.
     *
     * @param \ChrisAndChris\Common\RowMapperBundle\Services\Query\Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function fetchSingle($int)
    {
        $entity = new InvoiceDemoEntity();
        $entity->set(OperationDemoEntity::class, new ResultSet(
            [
                new OperationDemoEntity(),
            ]
        ));

        return $entity;
    }

    public function demoFetch()
    {
        $query = $this->builder->entity(InvoiceDemoEntity::class)
                               ->where()
                               ->field('invoice_id')
                               ->equals()
                               ->value(1)
                               ->close();

        return $query->getSqlQuery();
    }
}
